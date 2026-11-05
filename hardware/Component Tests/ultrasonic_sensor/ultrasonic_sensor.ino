
#define TRIG_PIN 5
#define ECHO_PIN 18
#define IR_PIN 27

void setup() {
  Serial.begin(115200);

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(IR_PIN, INPUT);

  digitalWrite(TRIG_PIN, LOW);

  Serial.println("SMART PUMP - SENSOR TEST");
}

void loop() {

  // Ultrasonic measurement
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);

  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);

  digitalWrite(TRIG_PIN, LOW);

  long duration = pulseIn(ECHO_PIN, HIGH, 30000);

  float distance = duration * 0.0343 / 2;

  // IR detection
  int irState = digitalRead(IR_PIN);

  Serial.println("--------------------------");

  if (duration == 0) {
    Serial.println("Ultrasonic: No echo detected");
  } else {
    Serial.print("Distance: ");
    Serial.print(distance);
    Serial.println(" cm");
  }

  Serial.print("IR Sensor: ");

  if (irState == LOW) {
    Serial.println("Object detected (possible)");
  } else {
    Serial.println("No object detected (possible)");
  }

  delay(500);
}
