// Dashboard frontend only.
// This is intentionally local/demo logic for now.
// Real data and pump commands will later come from PHP APIs.

const dashboardState = {
    waterLevel: 1875,
    tankCapacity: 2500,
    flowRate: 0,
    pumpStatus: "OFF",
    consumption: 125
};

function updateDashboard() {
    const percentage = Math.round(
        (dashboardState.waterLevel / dashboardState.tankCapacity) * 100
    );

    document.getElementById("waterLevel").textContent = `${percentage}%`;
    document.getElementById("waterAmount").textContent =
        `${dashboardState.waterLevel} / ${dashboardState.tankCapacity} L`;

    document.getElementById("overviewWater").textContent =
        `${dashboardState.waterLevel} L`;

    document.getElementById("flowRate").textContent =
        `${dashboardState.flowRate} L/min`;

    document.getElementById("overviewFlow").textContent =
        `${dashboardState.flowRate} L/min`;

    document.getElementById("pumpStatus").textContent =
        dashboardState.pumpStatus;

    document.getElementById("pumpMode").textContent =
        dashboardState.pumpStatus === "ON" ? "Running" : "Standby Mode";

    document.getElementById("consumption").textContent =
        `${dashboardState.consumption} L`;

    document.getElementById("controlMessage").textContent =
        dashboardState.pumpStatus === "ON"
            ? "Pump is running (frontend demo state)"
            : "Pump is currently stopped";

    document.getElementById("waterStatusText").textContent =
        percentage <= 20 ? "Low" : "Normal";
}

function setPumpState(state) {
    dashboardState.pumpStatus = state;
    updateDashboard();

    document.getElementById("controlMessage").textContent =
        state === "ON"
            ? "Demo mode: pump state changed to ON locally."
            : "Demo mode: pump state changed to OFF locally.";
}

function updateDateTime() {
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
}

document.addEventListener("DOMContentLoaded", () => {
    updateDashboard();
    updateDateTime();
    setInterval(updateDateTime, 1000);

    document.getElementById("startPump").addEventListener("click", () => {
        setPumpState("ON");
    });

    document.getElementById("stopPump").addEventListener("click", () => {
        setPumpState("OFF");
    });

    document.getElementById("menuButton").addEventListener("click", () => {
        document.querySelector(".sidebar").classList.toggle("open");
    });
});