
const STATUS_URL = "../backend/device/status.php";

async function updateDeviceStatus() {

    try {

        const response = await fetch(STATUS_URL, {
            method: "GET",
            cache: "no-store"
        });

        if (!response.ok) {
            throw new Error("HTTP " + response.status);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        // Pump status
        const pumpStatus = data.pump.status;

        document.getElementById("controlPumpState")
            .textContent = pumpStatus;

        document.getElementById("readingPump")
            .textContent = pumpStatus;

        // Distance
        document.getElementById("readingDistance")
            .textContent =
            data.sensors.distance_cm !== null
                ? data.sensors.distance_cm + " cm"
                : "-- cm";

        // Container detection
        document.getElementById("readingContainer")
            .textContent =
            data.sensors.container_detected === null
                ? "--"
                : data.sensors.container_detected
                    ? "DETECTED"
                    : "ABSENT";

        // Controller connection
        document.getElementById("readingController")
            .textContent =
            data.device.online ? "ONLINE" : "OFFLINE";

        // Connection indicator
        document.getElementById("deviceOnlineText")
            .textContent =
            data.device.online
                ? "ESP32 Online"
                : "ESP32 Offline";

        document.getElementById("deviceOnlineDot")
            .style.backgroundColor =
            data.device.online ? "#16a34a" : "#dc2626";

        // Last telemetry
        document.getElementById("lastTelemetry")
            .textContent =
            data.last_update
                ? "Last update: " + data.last_update
                : "No telemetry received yet";

    } catch (error) {

        console.error("Device monitoring error:", error);

        document.getElementById("deviceOnlineText")
            .textContent = "Connection Error";

        document.getElementById("readingController")
            .textContent = "OFFLINE";
    }
}

// Initial update
updateDeviceStatus();

// Refresh every 2 seconds
setInterval(updateDeviceStatus, 2000);
