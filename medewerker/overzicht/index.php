<?php
// Database verbinding laden
require_once 'db.php';

// Zoek functie
$zoekterm = "";
if (isset($_GET['zoek'])) {
    $zoekterm = $_GET['zoek'];
    $sql = "SELECT * FROM medewerkers WHERE naam LIKE '%$zoekterm%' OR functie LIKE '%$zoekterm%'";
} else {
    $sql = "SELECT * FROM medewerkers";
}

// Query uitvoeren
$resultaat = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Theater - Medewerkers</title>
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- ========= NAVBAR MET HAMBURGER MENU (zelfde als hoofdpagina) ========= -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <span class="logo-icon">★</span> Aurora Theater
            </div>

            <button class="menu-toggle" id="mobile-menu" aria-label="Open menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>

            <div class="nav-menu" id="nav-menu">
                <ul class="nav-links">
                    <li><a href="../../index.php" class="active">Home</a></li>
                    <li><a href="#">Informatie</a></li>
                    <li><a href="../../meldingen.php">Meldingen</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
                <div class="nav-buttons">
                    <a href="/Aurora/meldingen.php" style="text-decoration: none;">
                        <button class="btn-notifications" id="btn-alerts" aria-label="Meldingen">
                            🔔 Meldingen
                        </button>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- ========= MAIN CONTENT ========= -->
    <div class="container">
        <div class="header">
            <h1><span class="logo-icon">A</span> Aurora Theater</h1>
            <p>Overzicht medewerkers</p>
        </div>

        <!-- Zoekformulier -->
        <div class="zoek-section">
            <form method="GET" class="zoek-form">
                <i class="fas fa-search"></i>
                <input type="text" name="zoek" placeholder="Zoek op naam of functie..."
                    value="<?php echo $zoekterm; ?>">
                <button type="submit">Zoeken</button>
                <?php if ($zoekterm != ""): ?>
                    <a href="index.php" class="wis-knop"><i class="fas fa-times"></i> Wis</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabel met medewerkers -->
        <div class="tabel-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>Functie</th>
                        <th>Afdeling</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultaat) > 0): ?>
                        <?php while ($rij = mysqli_fetch_assoc($resultaat)): ?>
                            <tr>
                                <td><?php echo $rij['id']; ?></td>
                                <td class="naam-cell">
                                    <div class="avatar-small"><?php echo strtoupper(substr($rij['naam'], 0, 1)); ?></div>
                                    <strong><?php echo htmlspecialchars($rij['naam']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($rij['funkcie'] ?? $rij['functie']); ?></td>
                                <td><span class="afdeling-badge"><?php echo htmlspecialchars($rij['afdeling']); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="geen-data">Geen medewerkers gevonden</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Statistiek -->
        <div class="stats">
            <p><i class="fas fa-users"></i> Totaal medewerkers:
                <strong><?php echo mysqli_num_rows($resultaat); ?></strong></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>✨ Ervaar de magie van live theater in Amsterdam ✨</p>
        </div>
    </div>

    <script>
        // Hamburger menu toggle
        const menuToggle = document.getElementById('mobile-menu');
        const navMenu = document.getElementById('nav-menu');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                menuToggle.classList.toggle('active');
                navMenu.classList.toggle('active');
            });
        }
    </script>

</body>

</html>

<?php
mysqli_close($conn);
?>