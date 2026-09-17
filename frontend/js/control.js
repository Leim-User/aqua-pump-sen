// Control Panel frontend only.
// No real hardware command is sent yet.
// PHP API integration will replace the local state changes.

let pumpState = "STOPPED";

function updatePumpUI() {
    document.getElementById("controlPumpState").textContent = pumpState;
    document.getElementById("readingPump").textContent = pumpState;

    document.getElementById("controlPageMessage").textContent =
        pumpState === "RUNNING"
            ? "Demo mode: pump state changed to RUNNING locally. PHP API is not connected yet."
            : "Demo mode: pump state changed to STOPPED locally. PHP API is not connected yet.";
}

document.addEventListener("DOMContentLoaded", () => {
    updatePumpUI();

    document.getElementById("controlStartPump").addEventListener("click", () => {
        pumpState = "RUNNING";
        updatePumpUI();
    });

    document.getElementById("controlStopPump").addEventListener("click", () => {
        pumpState = "STOPPED";
        updatePumpUI();
    });

    document.getElementById("menuButton").addEventListener("click", () => {
        document.querySelector(".sidebar").classList.toggle("open");
    });

    setInterval(() => {
        const now = new Date();

        document.getElementById("currentDate").textContent =
            now.toLocaleDateString("en-GB", {
                day: "2-digit",
                month: "short",
                year: "numeric"
            });

        document.getElementById("currentTime").textContent =
            now.toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit"
            });

        document.getElementById("lastUpdate").textContent = "Just now";
    }, 1000);
});