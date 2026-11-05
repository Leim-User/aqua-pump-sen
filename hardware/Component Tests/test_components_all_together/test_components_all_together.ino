
#define RELAY_PIN 26
#define TRIG_PIN 5
#define ECHO_PIN 18
#define IR_PIN 27

#define RELAY_ON LOW
#define RELAY_OFF HIGH

// Adjust these according to your dispenser
#define START_DISTANCE 15.0
#define STOP_DISTANCE 5.0

#define MAX_PUMP_TIME 10000UL

bool pumpRunning = false;
unsigned long pumpStartTime = 0;

float getDistance() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);

  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);

  digitalWrite(TRIG_PIN, LOW);

  long duration = pulseIn(ECHO_PIN, HIGH, 30000);

  if (duration == 0) return -1;

  return duration * 0.0343 / 2;
}

void pumpOn() {
  digitalWrite(RELAY_PIN, RELAY_ON);
  pumpRunning = true;
  pumpStartTime = millis();

  Serial.println("PUMP ON");
}

void pumpOff() {
  digitalWrite(RELAY_PIN, RELAY_OFF);
  pumpRunning = false;

  Serial.println("PUMP OFF");
}

void setup() {
  Serial.begin(115200);

  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, RELAY_OFF);

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(IR_PIN, INPUT);

  Serial.println("SMART WATER DISPENSER READY");
}

void loop() {
  bool containerDetected = digitalRead(IR_PIN) == LOW;
  float distance = getDistance();

  Serial.print("Container: ");
  Serial.println(containerDetected ? "DETECTED" : "ABSENT");

  Serial.print("Distance: ");
  Serial.println(distance);

  // Safety: no container or invalid measurement
  if (!containerDetected || distance < 0) {
    if (pumpRunning) pumpOff();
    delay(200);
    return;
  }

  // Stop when target level is reached
  if (pumpRunning && distance <= STOP_DISTANCE) {
    pumpOff();
  }

  // Start pumping when distance is above target
  else if (!pumpRunning && distance >= START_DISTANCE) {
    pumpOn();
  }

  // Maximum pumping duration
  if (pumpRunning &&
      millis() - pumpStartTime >= MAX_PUMP_TIME) {
    pumpOff();
    Serial.println("SAFETY TIMEOUT!");
  }

  delay(200);
}
