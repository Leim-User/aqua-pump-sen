
document.addEventListener("DOMContentLoaded", function () {
    const menuButton = document.querySelector(".menu-button");
    const sidebar = document.querySelector(".sidebar");

    if (!menuButton || !sidebar) {
        return;
    }

    // Toggle the sidebar when the menu button is clicked
    menuButton.addEventListener("click", function () {
        sidebar.classList.toggle("sidebar-open");
    });

    // Close the sidebar when a navigation link is clicked
    const navigationLinks = sidebar.querySelectorAll(".navigation a");

    navigationLinks.forEach(function (link) {
        link.addEventListener("click", function () {
            sidebar.classList.remove("sidebar-open");
        });
    });

    // Close the sidebar when clicking outside it on mobile
    document.addEventListener("click", function (event) {
        const isMobile = window.matchMedia("(max-width: 768px)").matches;

        if (
            isMobile &&
            sidebar.classList.contains("sidebar-open") &&
            !sidebar.contains(event.target) &&
            !menuButton.contains(event.target)
        ) {
            sidebar.classList.remove("sidebar-open");
        }
    });

    // Close the mobile sidebar when the screen becomes wider
    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
            sidebar.classList.remove("sidebar-open");
        }
    });
});
