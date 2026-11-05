
#include <WiFi.h>
#include <HTTPClient.h>
#include <esp_system.h>

// =============================================
// 1. WIFI CONFIGURATION
// =============================================

const char* WIFI_SSID = "Wifi2";
const char* WIFI_PASSWORD = "#Lema@1234";

const char* SERVER_URL =
"http://172.20.109.13/aqua-pump/backend/device/telemetry.php";

// =============================================
// 2. HARDWARE CONFIGURATION
// =============================================

#define RELAY_PIN 26
#define TRIG_PIN 5
#define ECHO_PIN 18
#define IR_PIN 27

// Active LOW relay
#define RELAY_ON HIGH
#define RELAY_OFF LOW

// IR sensor detection polarity
#define IR_DETECTED LOW

// =============================================
// 3. CONTROL PARAMETERS
// =============================================

const float START_DISTANCE = 15.0;
const float STOP_DISTANCE = 5.0;

const unsigned long MAX_PUMP_TIME = 60000;
const unsigned long TELEMETRY_INTERVAL = 2000;

const unsigned long WIFI_TIMEOUT = 15000;

// =============================================
// 4. SYSTEM VARIABLES
// =============================================

bool pumpRunning = false;
bool containerDetected = false;

bool timeoutLatched = false;

float distance = -1;

unsigned long pumpStartTime = 0;
unsigned long lastTelemetry = 0;

String fault = "NONE";

// =============================================
// 5. PUMP CONTROL
// =============================================

void pumpOn() {

    // Never restart after a safety timeout
    if (pumpRunning || timeoutLatched) {
        return;
    }

    digitalWrite(RELAY_PIN, RELAY_ON);

    pumpRunning = true;
    pumpStartTime = millis();

    Serial.println("PUMP: ON");
}


void pumpOff(const char* reason) {

    // Always command relay OFF
    digitalWrite(RELAY_PIN, RELAY_OFF);

    if (pumpRunning) {

        pumpRunning = false;

        Serial.print("PUMP: OFF - ");
        Serial.println(reason);
    }
}

// =============================================
// 6. ULTRASONIC SENSOR
// =============================================

float getDistance() {

    digitalWrite(TRIG_PIN, LOW);
    delayMicroseconds(2);

    digitalWrite(TRIG_PIN, HIGH);
    delayMicroseconds(10);

    digitalWrite(TRIG_PIN, LOW);

    unsigned long duration =
        pulseIn(ECHO_PIN, HIGH, 30000);

    if (duration == 0) {
        return -1;
    }

    return duration * 0.0343 / 2.0;
}

// =============================================
// 7. WIFI CONNECTION
// =============================================

void connectWiFi() {

    if (WiFi.status() == WL_CONNECTED) {
        return;
    }

    Serial.println();
    Serial.print("Connecting to WiFi");

    WiFi.mode(WIFI_STA);
    WiFi.setSleep(false);

    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    unsigned long startTime = millis();

    while (
        WiFi.status() != WL_CONNECTED &&
        millis() - startTime < WIFI_TIMEOUT
    ) {

        delay(500);
        Serial.print(".");
    }

    if (WiFi.status() == WL_CONNECTED) {

        Serial.println("\nWiFi connected!");

        Serial.print("ESP32 IP: ");
        Serial.println(WiFi.localIP());

        Serial.print("Server URL: ");
        Serial.println(SERVER_URL);

    } else {

        Serial.println("\nWiFi connection failed.");
    }
}

// =============================================
// 8. SEND TELEMETRY TO PHP
// =============================================

void sendTelemetry() {

    if (WiFi.status() != WL_CONNECTED) {

        Serial.println("Telemetry skipped: WiFi disconnected.");

        return;
    }

    HTTPClient http;

    http.setConnectTimeout(5000);
    http.setTimeout(8000);

    if (!http.begin(SERVER_URL)) {

        Serial.println("HTTP initialization failed.");

        return;
    }

    http.addHeader(
        "Content-Type",
        "application/json"
    );

    // Build JSON payload
    String json = "{";

    json += "\"device_id\":\"DISPENSER_001\",";
    json += "\"mode\":\"AUTO\",";

    json += "\"bucket_detected\":";
    json += containerDetected ? "true," : "false,";

    json += "\"distance_cm\":";

    if (distance >= 0) {
        json += String(distance, 2);
    } else {
        json += "null";
    }

    json += ",";

    json += "\"pump_status\":";
    json += pumpRunning ? "\"ON\"," : "\"OFF\",";

    json += "\"fault\":\"";
    json += fault;
    json += "\"";

    json += "}";

    Serial.println("\nSending telemetry...");
    Serial.println(json);

    // Send only ONE POST request
    int response = http.POST(json);

    Serial.print("HTTP Response: ");
    Serial.println(response);

    if (response > 0) {

        String serverResponse = http.getString();

        Serial.println("Server Response:");
        Serial.println(serverResponse);

    } else {

        Serial.print("HTTP Error: ");
        Serial.println(http.errorToString(response));
    }

    http.end();
}

// =============================================
// 9. SETUP
// =============================================

void setup() {

    Serial.begin(115200);

    // Initialize relay to OFF
    digitalWrite(RELAY_PIN, RELAY_OFF);
    pinMode(RELAY_PIN, OUTPUT);
    digitalWrite(RELAY_PIN, RELAY_OFF);

    // Initialize sensors
    pinMode(TRIG_PIN, OUTPUT);
    pinMode(ECHO_PIN, INPUT);
    pinMode(IR_PIN, INPUT);

    digitalWrite(TRIG_PIN, LOW);

    Serial.println();
    Serial.println("================================");
    Serial.println("SMART WATER DISPENSER");
    Serial.println("Operating Mode: AUTOMATIC");
    Serial.println("================================");

    Serial.print("ESP32 Reset Reason: ");
    Serial.println((int)esp_reset_reason());

    Serial.println("Relay initialized: OFF");

    connectWiFi();
}

// =============================================
// 10. MAIN LOOP
// =============================================

void loop() {

    // -----------------------------------------
    // READ SENSORS
    // -----------------------------------------

    containerDetected =
        digitalRead(IR_PIN) == IR_DETECTED;

    distance = getDistance();

    // -----------------------------------------
    // DISPLAY SENSOR INFORMATION
    // -----------------------------------------

    Serial.println();
    Serial.println("-----------------------");

    Serial.print("Container: ");
    Serial.println(
        containerDetected ? "DETECTED" : "ABSENT"
    );

    Serial.print("Distance: ");

    if (distance >= 0) {
        Serial.print(distance, 2);
        Serial.println(" cm");
    } else {
        Serial.println("INVALID");
    }

    // -----------------------------------------
    // AUTOMATIC CONTROL AND SAFETY
    // -----------------------------------------

    if (timeoutLatched) {

        fault = "PUMP_TIMEOUT";

        pumpOff("TIMEOUT LOCKOUT");
    }

    else if (!containerDetected) {

        fault = "NONE";

        pumpOff("CONTAINER REMOVED");
    }

    else if (distance < 0) {

        fault = "ULTRASONIC_ERROR";

        pumpOff("SENSOR ERROR");
    }

    else {

        fault = "NONE";

        // START FILLING
        if (
            !pumpRunning &&
            distance >= START_DISTANCE
        ) {

            pumpOn();
        }

        // STOP FILLING
        else if (
            pumpRunning &&
            distance <= STOP_DISTANCE
        ) {

            pumpOff("TARGET REACHED");
        }
    }

    // -----------------------------------------
    // MAXIMUM PUMPING DURATION
    // -----------------------------------------

    if (
        pumpRunning &&
        millis() - pumpStartTime >= MAX_PUMP_TIME
    ) {

        timeoutLatched = true;

        fault = "PUMP_TIMEOUT";

        pumpOff("MAXIMUM RUN TIME");
    }

    // -----------------------------------------
    // DISPLAY PUMP STATUS
    // -----------------------------------------

    Serial.print("Pump: ");
    Serial.println(pumpRunning ? "ON" : "OFF");

    Serial.print("Fault: ");
    Serial.println(fault);

    // -----------------------------------------
    // WIFI RECONNECTION
    // -----------------------------------------

    if (WiFi.status() != WL_CONNECTED) {

        connectWiFi();
    }

    // -----------------------------------------
    // SEND TELEMETRY
    // -----------------------------------------

    if (
        millis() - lastTelemetry >=
        TELEMETRY_INTERVAL
    ) {

        lastTelemetry = millis();

        sendTelemetry();
    }

    delay(200);
}
