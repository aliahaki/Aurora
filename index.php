<?php
// ==========================================================================
// DATABASE INSTELLINGEN VOOR WAMP
// ==========================================================================
$host = '127.0.0.1';        // Localhost IP voor WAMP
$dbname = 'aurora_theater'; // aangemaakte database
$username = 'root';         // Standaard WAMP gebruikersnaam
$password = '';             // Standaard WAMP wachtwoord (leeg)

$systeemFout = false;       // Standaard staat de fout uit (Happy Scenario)

try {
    // Probeer verbinding te maken met de MySQL database via PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Foutmeldingen aanzetten voor debugging
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Als de database offline is of niet bestaat: Unhappy Scenario wordt actief!
    $systeemFout = true;
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Theater - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="error-banner <?php echo $systeemFout ? 'active' : ''; ?>" id="error-message">
        De pagina kan momenteel niet geladen worden. Probeer het later opnieuw.
    </div>

    <header>
        <nav class="navbar">
            <div class="logo"><span class="logo-icon">★</span> Aurora Theater</div>

            <button class="menu-toggle" id="mobile-menu" aria-label="Open menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>

            <div class="nav-menu" id="nav-menu">
                <ul class="nav-links">
                    <li><a href="#" class="active">Home</a></li>
                    <li><a href="#">Informatie</a></li>
                    <li><a href="#">Over Ons</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
                <div class="nav-buttons">
                    <button class="btn-notifications" id="btn-alerts" aria-label="Meldingen">
                        🔔 Meldingen
                    </button>
                    <button class="btn-login">Login</button>
                    <button class="btn-register">Registreren</button>
                </div>
            </div>
        </nav>
    </header>

    <section class="hero-container">
        <div class="hero-images-grid">
            <div class="hero-img-box pic-1"></div>
            <div class="hero-img-box pic-2"></div>
            <div class="hero-img-box pic-3"></div>
        </div>
        <div class="hero-text-overlay">
            <div class="hero-content">
                <h1>Welkom bij<br>Aurora Theater</h1>
                <p>Ervaar de magie van live theater en muziek in het hart van Amsterdam</p>
                <button class="btn-tickets">🎫 Tickets reserveren</button>
            </div>
        </div>
    </section>

    <section class="info-section">
        <div class="container">
            <div class="info-text">
                <span class="section-tag">Pure Magie</span>
                <h2>Een Onvergetelijke Avond Uit</h2>
                <p>Al meer dan 25 jaar is Aurora Theater het culturele hart van Amsterdam. Stap binnen in onze historische zaal en laat je meeslepen door adembenemende verhalen, prachtige live muziek en intieme cabaretvoorstellingen.</p>
                <p>Van grootschalige moderne concerten tot klassieke toneelstukken van topniveau; bij ons zit je altijd bovenop de actie. Wij geloven dat theater je moet raken, verrassen en inspireren. Kom langs en geniet van een compleet verzorgde avond uit met heerlijke hapjes en drankjes.</p>
                <button class="btn-more">Ontdek Onze Geschiedenis</button>
            </div>
            <div class="info-image main-info-pic"></div>
        </div>
    </section>

    <section class="gallery-section">
        <div class="gallery-header">
            <h2>Blik Achter de Schermen</h2>
            <p>Proef de sfeer van onze zalen, de artiesten en het publiek.</p>
        </div>
        <div class="gallery-grid">
            <div class="gallery-item gal-1"></div>
            <div class="gallery-item gal-2"></div>
            <div class="gallery-item gal-3"></div>
        </div>
    </section>

    <section class="reviews-section">
        <h2>Wat Bezoekers Over Ons Zeggen</h2>
        <p class="section-subtitle">Echte ervaringen van theaterliefhebbers</p>
        <div class="reviews-grid">
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p class="review-text">"Wat een magische plek! De akoestiek in de grote zaal is werkelijk geweldig en de stoelen zitten heerlijk."</p>
                <div class="review-author"><strong>Marjolein de Vries</strong><span>Bezoeker</span></div>
            </div>
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p class="review-text">"De sfeer in de zaal is zo intiem. Je bent echt verbonden met de acteurs op het podium. Prachtig historisch gebouw!"</p>
                <div class="review-author"><strong>Michael S.</strong><span>Trouwe Fan</span></div>
            </div>
            <div class="review-card">
                <div class="stars">★★★★☆</div>
                <p class="review-text">"Heerlijke avond gehad bij de Jazz Night. Drankjes zijn goed geregeld und het personeel is ontzettend vriendelijk."</p>
                <div class="review-author"><strong>Sarah J.</strong><span>Bezoeker</span></div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <h3><span class="logo-icon">★</span> Aurora Theater</h3>
                <p>Het thuis van de mooiste cultuur- en muziekvoorstellingen.</p>
            </div>
            <div class="footer-contact">
                <h4>Contact & Adres</h4>
                <p>📍 Theaterplein 1, 1011 VX Amsterdam</p>
                <p>📞 020 - 123 4567</p>
                <p>✉️ info@auroratheater.nl</p>
            </div>
            <div class="footer-hours">
                <h4>Openingstijden Kassa</h4>
                <p>Maandag - Vrijdag: 14:00 - 22:00</p>
                <p>Zaterdag & Zondag: 12:00 - 23:00</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Aurora Theater. Alle rechten voorbehouden.</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>

</html>