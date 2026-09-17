// Small frontend helper for the Alerts page.

document.addEventListener("DOMContentLoaded", () => {
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