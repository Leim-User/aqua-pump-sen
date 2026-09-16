const dashboardState = {
    waterLevel: 1875,
    tankCapacity: 2500,
    flowRate: 0,
    pumpStatus: "OFF",
    consumption: 125
};

function updateDashboard() {
    const percentage = Math.round((dashboardState.waterLevel / dashboardState.tankCapacity) * 100);
    
    document.getElementById("waterLevel").textContent = `${percentage}%`;

    document.getElementById("waterAmount").textContent = `${dashboardState.waterLevel} / ${dashboardState.tankCapacity} L`;

    document.getElementById("overviewWater").textContent = `${dashboardState.waterLevel} L`;

    document.getElementById("flowRate").textContent = `${dashboardState.flowRate} L/min`;

    document.getElementById("overviewFlow").textContent = `${dashboardState.flowRate} L/min`;

    document.getElementById("pumpStatus").textContent = dashboardState.pumpStatus;

    document.getElementById()

}