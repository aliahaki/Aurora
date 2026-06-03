<?php
// ==========================================================================
// DATABASE INSTELLINGEN VOOR WAMP
// ==========================================================================
$host = '127.0.0.1';
$dbname = 'aurora_theater';
$username = 'root';
$password = '';

$systeemFout = false;
$notifications = [];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verwerkt het formulier als er een nieuwe melding wordt gepost
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_notification') {
        $title = trim($_POST['title']);
        $message = trim($_POST['message']);
        $type = 'Nieuws';
        $created_at = date('Y-m-d');

        if (!empty($title) && !empty($message)) {
            $insertStmt = $conn->prepare("INSERT INTO notifications (title, message, type, created_at) VALUES (:title, :message, :type, :created_at)");
            $insertStmt->execute([
                ':title' => $title,
                ':message' => $message,
                ':type' => $type,
                ':created_at' => $created_at
            ]);
        }
    }

    // Haal alle meldingen op uit de database
    $stmt = $conn->prepare("SELECT * FROM notifications ORDER BY created_at DESC");
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $systeemFout = true;
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Theater - Meldingen</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#">Informatie</a></li>
                    <li><a href="meldingen.php" class="active">Meldingen</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
                <div class="nav-buttons">
                    <a href="meldingen.php" style="text-decoration: none;">
                        <button class="btn-notifications" id="btn-alerts" aria-label="Meldingen">
                            🔔 Meldingen
                        </button>
                    </a>
                    <button class="btn-login">Login</button>
                    <button class="btn-register">Registreren</button>
                </div>
            </div>
        </nav>
    </header>

    <main class="notifications-container">
        <h1 style="text-align: center; margin-bottom: 40px; font-size: 48px;">Meldingen</h1>

        <div style="margin-bottom: 50px;">
            <h2 style="font-size: 28px; margin-bottom: 25px; font-family: 'Playfair Display', serif;">Recente Meldingen</h2>

            <?php if (!$systeemFout && !empty($notifications)): ?>
                <?php foreach ($notifications as $notif):
                    $typeClass = strtolower($notif['type']);
                    if ($typeClass == 'waarschuwing') {
                        $typeClass = 'warning';
                        $icon = 'fa-bell';
                    } elseif ($typeClass == 'nieuws') {
                        $typeClass = 'news';
                        $icon = 'fa-volume-high';
                    } else {
                        $typeClass = 'info';
                        $icon = 'fa-circle-info';
                    }
                ?>
                    <div class="card-notification <?php echo $typeClass; ?>">
                        <div class="card-icon-wrapper <?php echo $typeClass; ?>">
                            <i class="fa-regular <?php echo $icon; ?>"></i>
                        </div>
                        <div class="card-body-content">
                            <div class="card-title-row">
                                <h3 style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 18px; margin: 0; color: var(--text-dark);"><?php echo htmlspecialchars($notif['title']); ?></h3>
                                <span class="badge <?php echo $typeClass; ?>"><?php echo htmlspecialchars($notif['type']); ?></span>
                            </div>
                            <p class="card-message"><?php echo htmlspecialchars($notif['message']); ?></p>
                            <span class="card-date"><?php echo date('j mei Y', strtotime($notif['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Er zijn momenteel geen meldingen beschikbaar of er is een databasefout.</p>
            <?php endif; ?>
        </div>

        <div class="form-card">
            <h2 style="margin-bottom: 20px;">Nieuwe Melding Maken</h2>
            <form action="meldingen.php" method="POST">
                <input type="hidden" name="action" value="new_notification">

                <div class="form-group">
                    <label>Titel</label>
                    <input type="text" name="title" placeholder="Titel van de melding" required>
                </div>

                <div class="form-group">
                    <label>Bericht</label>
                    <textarea name="message" rows="5" placeholder="Uw bericht..." required></textarea>
                </div>

                <button type="submit" class="btn-submit-purple">Melding Versturen</button>
            </form>
        </div>

        <div class="form-card">
            <h2 style="margin-bottom: 10px;">Feedback</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
                We waarderen uw feedback! Laat ons weten wat u van onze service vindt.
            </p>
            <form action="#" method="POST">
                <div class="form-group">
                    <textarea rows="4" placeholder="Deel uw feedback met ons..." required></textarea>
                </div>
                <button type="submit" class="btn-submit-dark">Feedback Verzenden</button>
            </form>
        </div>
    </main>

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