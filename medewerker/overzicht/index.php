<?php
// Database verbinding laden
require_once 'db.php';

// Check of database werkt
$db_beschikbaar = true;
$foutmelding = "";

// Check connectie
if (!isset($conn) || !$conn) {
    $db_beschikbaar = false;
    $foutmelding = "Database verbinding mislukt.";
} else {
    // Check of tabel bestaat
    $check_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'medewerkers'");
    if (!$check_tabel || mysqli_num_rows($check_tabel) == 0) {
        $db_beschikbaar = false;
        $foutmelding = "Tabel 'medewerkers' bestaat niet.";
    }
}

// Zoeken (alleen als database werkt)
$zoekterm = "";
$resultaat = null;
$totaal = 0;

if ($db_beschikbaar) {
    if (isset($_GET['zoek'])) {
        $zoekterm = $_GET['zoek'];
        $sql = "SELECT * FROM medewerkers WHERE naam LIKE '%$zoekterm%' OR functie LIKE '%$zoekterm%'";
    } else {
        $sql = "SELECT * FROM medewerkers";
    }
    
    $resultaat = mysqli_query($conn, $sql);
    
    if (!$resultaat) {
        $db_beschikbaar = false;
        $foutmelding = "Fout bij ophalen gegevens.";
    } else {
        $totaal = mysqli_num_rows($resultaat);
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Theater - Medewerkers</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigatie balk -->
<header>
    <nav class="navbar">
        <div class="logo">
            <span class="logo-icon">★</span> Aurora Theater
        </div>

        <!-- Hamburger menu knop -->
        <button class="menu-toggle" id="mobile-menu">
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

<!-- Hoofdinhoud -->
<div class="container">
    
    <!-- Titel -->
    <div class="header">
        <h1><span class="logo-icon">A</span> Aurora Theater</h1>
        <p>Overzicht medewerkers</p>
    </div>
    
    <!-- FOUTMELDING (als database niet werkt) -->
    <?php if (!$db_beschikbaar): ?>
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-database"></i>
            </div>
            <h2>Database niet beschikbaar</h2>
            <p><?php echo $foutmelding; ?></p>
            <button class="btn-retry" onclick="window.location.href='index.php'">
    <i class="fas fa-sync-alt"></i> Opnieuw proberen
</button>
        </div>
    
    <!-- DATABASE WERKT -->
    <?php else: ?>
        
        <!-- Zoekbalk -->
        <div class="zoek-section">
            <form method="GET" class="zoek-form">
                <i class="fas fa-search"></i>
                <input type="text" name="zoek" placeholder="Zoek op naam of functie..." value="<?php echo htmlspecialchars($zoekterm); ?>">
                <button type="submit">Zoeken</button>
                <?php if ($zoekterm != ""): ?>
                    <a href="index.php" class="wis-knop"><i class="fas fa-times"></i> Wis</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Tabel met medewerkers -->
        <div class="tabel-container">
            <?php if ($totaal > 0): ?>
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
                        <?php while($rij = mysqli_fetch_assoc($resultaat)): ?>
                            <tr>
                                <td><?php echo $rij['id']; ?></td>
                                <td class="naam-cell">
                                    <div class="avatar-small"><?php echo strtoupper(substr($rij['naam'], 0, 1)); ?></div>
                                    <strong><?php echo htmlspecialchars($rij['naam']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($rij['functie']); ?></td>
                                <td><span class="afdeling-badge"><?php echo htmlspecialchars($rij['afdeling']); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="geen-data">
                    <i class="fas fa-user-slash"></i>
                    <p>Geen medewerkers gevonden</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Totaal aantal -->
        <div class="stats">
            <p><i class="fas fa-users"></i> Totaal medewerkers: <strong><?php echo $totaal; ?></strong></p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>✨ Ervaar de magie van live theater in Amsterdam ✨</p>
        </div>
        
    <?php endif; ?>
</div>

<!-- Javascript voor hamburger menu -->
<script>
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
if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>