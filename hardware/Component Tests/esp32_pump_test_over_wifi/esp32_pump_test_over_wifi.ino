#include <WiFi.h>
#include <HTTPClient.h>

const char* WIFI_SSID = "TECNO SPARK 4";
const char* WIFI_PASS = "nicole love";
const char* API_URL = "http://10.34.211.13:8000/backend/usage/test_api_wifi.php";  // use YOUR PC's IP

const int LED_PIN = 2;  // built-in LED on most ESP32 boards

void setup() {
  Serial.begin(115200);
  pinMode(LED_PIN, OUTPUT);

  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.print("\nConnected. ESP32 IP: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(API_URL);
    http.setTimeout(3000);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    int code = http.POST("level=" + String(random(0, 101)));  // fake sensor value

    if (code == 200) {
      String command = http.getString();
      command.trim();
      Serial.println("Command from server: " + command);
      digitalWrite(LED_PIN, command == "ON" ? HIGH : LOW);
    } else {
      Serial.println("HTTP error: " + String(code));
    }
    http.end();
  }
  delay(3000);  // ask the server every 3 seconds
}