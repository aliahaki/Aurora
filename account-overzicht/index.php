<?php

// Haal de database verbinding erbij
require_once './db.php';

// Zoek functie (als er gezocht wordt)
$zoekterm = "";
if (isset($_GET['zoek'])) {
    $zoekterm = $_GET['zoek'];
    $sql = "SELECT * FROM users WHERE username LIKE '%$zoekterm%' OR email LIKE '%$zoekterm%'";
} else {
    $sql = "SELECT * FROM users";
}

$resultaat = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Aurora Theater - Gebruikers</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Aurora Theater</h1>
        <p>Overzicht gebruikers</p>
    </div>
    
    <!-- Zoekformulier -->
    <div class="zoek-section">
        <form method="GET" class="zoek-form">
            <input type="text" name="zoek" placeholder="Zoek op gebruikersnaam of e-mail..." value="<?php echo $zoekterm; ?>">
            <button type="submit">Zoeken</button>
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
                    <th>Gebruikersnaam</th>
                    <th>E-mail</th>
                    <th>Join datum</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultaat) > 0): ?>
                    <?php while($rij = mysqli_fetch_assoc($resultaat)): ?>
                        <tr>
                            <td><?php echo $rij['Id']; ?></td>
                            <td><strong><?php echo $rij['username']; ?></strong></td>
                            <td><?php echo $rij['email']; ?></td>
                            <td><?php echo $rij['join_date']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="geen-data">Geen gebruikers gevonden</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Statistiek -->
    <div class="stats">
        <p>Totaal gebruikers: <strong><?php echo mysqli_num_rows($resultaat); ?></strong></p>
    </div>
    
    <div class="footer">
        <p>✨ Ervaar de magie van live theater in Amsterdam ✨</p>
    </div>
</div>

</body>
</html>

<?php
// Sluit de database verbinding
mysqli_close($conn);
?>