const int FLOW_PIN = 18;

volatile unsigned long pulses = 0;

void IRAM_ATTR countPulse() {
  pulses++;
}

void setup() {
  // put your setup code here, to run once:
  Serial.begin(115200);

  pinMode(FLOW_PIN, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(FLOW_PIN),
  countPulse,
  FALLING
  );

  Serial.println("Flow sensor test started.");
}

void loop() {
  // put your main code here, to run repeatedly:
  noInterrupts();
  unsigned long count = pulses;
  pulses = 0;
  interrupts();

  Serial.print("Pulses in the last 5 seconds: ");
  Serial.println(count);

  delay(5000);
}
