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
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <!-- Header / Titel -->
    <div class="header">
        <h1>Aurora Theater</h1>
        <p>Overzicht medewerkers</p>
    </div>
    
    <!-- Zoekformulier -->
    <div class="zoek-section">
        <form method="GET" class="zoek-form">
            <input type="text" name="zoek" placeholder="Zoek op naam of functie..." value="<?php echo $zoekterm; ?>">
            <button type="submit">Zoeken</button>
            <!-- Wis knop (alleen zichtbaar als er gezocht wordt) -->
            <?php if ($zoekterm != ""): ?>
                <a href="index.php" class="wis-knop">Wis</a>
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
                    <!-- Loop door alle medewerkers heen -->
                    <?php while($rij = mysqli_fetch_assoc($resultaat)): ?>
                        <tr>
                            <td><?php echo $rij['id']; ?></td>
                            <td><strong><?php echo $rij['naam']; ?></strong></td>
                            <td><?php echo $rij['functie']; ?></td>
                            <td><?php echo $rij['afdeling']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Geen resultaten gevonden -->
                    <tr>
                        <td colspan="4" class="geen-data">Geen medewerkers gevonden</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Statistiek: totaal aantal -->
    <div class="stats">
        <p>Totaal medewerkers: <strong><?php echo mysqli_num_rows($resultaat); ?></strong></p>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>✨ Ervaar de magie van live theater in Amsterdam ✨</p>
    </div>
</div>

</body>
</html>

<?php
// Database verbinding sluiten
mysqli_close($conn);
?>