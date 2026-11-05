#define RELAY_PIN 26

void setup() {
  Serial.begin(115200);

  pinMode(RELAY_PIN, OUTPUT);

  // Start with relay OFF for active-LOW modules
  digitalWrite(RELAY_PIN, HIGH);

  Serial.println("Relay Test Started");
}

void loop() {

  // Test active-LOW
  Serial.println("Testing ACTIVE-LOW...");
  digitalWrite(RELAY_PIN, LOW);
  delay(2000);

  digitalWrite(RELAY_PIN, HIGH);
  delay(2000);

  // Test active-HIGH
  Serial.println("Testing ACTIVE-HIGH...");
  digitalWrite(RELAY_PIN, HIGH);
  delay(2000);

  digitalWrite(RELAY_PIN, LOW);
  delay(2000);
}