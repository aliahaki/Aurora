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

document.addEventListener("DOMContentLoaded", function () {
    const tabVersturen = document.getElementById("tab-versturen");
    const tabOntvangen = document.getElementById("tab-ontvangen");
    const formContainer = document.getElementById("feedback-form-container");
    const lijstContainer = document.getElementById("feedback-lijst-container");

    // Als je op "Feedback Versturen" klikt
    tabVersturen.addEventListener("click", function () {
        tabVersturen.classList.add("active");
        tabOntvangen.classList.remove("active");

        formContainer.classList.remove("hidden");  // Toon formulier
        lijstContainer.classList.add("hidden");    // Verberg lijst
    });

    // Als je op "Ontvangen Feedback" klikt
    tabOntvangen.addEventListener("click", function () {
        tabOntvangen.classList.add("active");
        tabVersturen.classList.remove("active");

        lijstContainer.classList.remove("hidden"); // Toon lijst
        formContainer.classList.add("hidden");     // Verberg formulier
    });
});

