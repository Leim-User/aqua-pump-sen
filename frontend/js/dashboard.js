let pumpStatus = "OFF";

function startPump() {

    pumpStatus = "ON";

    document.getElementById("pumpStatus").textContent = "ON";

    document.getElementById("alert").textContent = "Pump is currently running";
}

function stopPump() {

    pumpStatus = "OFF";

    document.getElementById("pumpStatus").textContent = "OFF";

    document.getElementById("alert").textContent = "Pump has been stopped";
}