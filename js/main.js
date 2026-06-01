// Wacht tot de browser de HTML volledig heeft ingeladen
window.addEventListener("load", () => {

    const menuToggle = document.getElementById("mobile-menu");
    const navMenu = document.getElementById("nav-menu");

    // Controleer of de hamburger elementen bestaan
    if (menuToggle && navMenu) {
        menuToggle.addEventListener("click", (event) => {
            event.preventDefault();
            // Voeg de '.active' klasse toe of haal hem weg
            menuToggle.classList.toggle("active");
            navMenu.classList.toggle("active");
        });

        // Sluit het menu automatisch bij een klik op een menu-link
        const navLinks = document.querySelectorAll(".nav-links a");
        navLinks.forEach(link => {
            link.addEventListener("click", () => {
                menuToggle.classList.remove("active");
                navMenu.classList.remove("active");
            });
        });
    }
});