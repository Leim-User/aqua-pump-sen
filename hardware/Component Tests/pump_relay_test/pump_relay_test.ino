
#define RELAY_PIN 26

// Change these if your relay uses the opposite polarity
#define RELAY_ON  LOW
#define RELAY_OFF HIGH

void setup() {
  Serial.begin(115200);

  digitalWrite(RELAY_PIN, RELAY_OFF);
  pinMode(RELAY_PIN, OUTPUT);

  Serial.println("SMART WATER PUMP TEST");
  Serial.println("Pump initially OFF");
}

void loop() {

  Serial.println("PUMP ON");
  digitalWrite(RELAY_PIN, RELAY_ON);

  delay(3000);

  Serial.println("PUMP OFF");
  digitalWrite(RELAY_PIN, RELAY_OFF);

  delay(5000);
}
