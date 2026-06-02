<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = '127.0.0.1';
$dbname = 'aurora_theater';
$username = 'root';
$password = '';

$systeemFout = false;
$notifications = [];

try {
    $conn = new PDO("mysql:host=$host;dbname=aurora_theater;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verwerk het formulier als er een nieuwe melding wordt verstuurd
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_notification') {
        $title = trim($_POST['title']);
        $message = trim($_POST['message']);
        $type = 'Nieuws'; // Standaard type, of voeg een select-veld toe
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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body style="background-color: #f8f9fc; margin: 0; font-family: 'Poppins', sans-serif;">

    <div class="error-banner <?php echo $systeemFout ? 'active' : ''; ?>">
        Meldingen kunnen momenteel niet worden geladen. Probeer het later opnieuw.
    </div>

    <header>
        <nav class="navbar">
            <div class="logo"><span class="logo-icon">★</span>Aurora Theater</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="#">Informatie</a></li>
                <li><a href="meldingen.php" class="active">Meldingen</a></li>
            </ul>
            <div class="nav-buttons">
                <button class="btn-login">Login</button>
                <button class="btn-register">Registreren</button>
            </div>
        </nav>
    </header>

    <main style="max-width: 1000px; margin: 0 auto; padding: 40px 20px;">
        <h1 style="text-align: center; color: #1e272c; font-size: 54px; font-weight: 700; margin-bottom: 50px; letter-spacing: -1px;">Meldingen</h1>

        <div style="margin-bottom: 50px;">
            <h2 style="font-size: 22px; color: #1e272c; margin-bottom: 25px; font-weight: 600;">Recente Meldingen</h2>

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
                                <h3><?php echo htmlspecialchars($notif['title']); ?></h3>
                                <span class="badge <?php echo $typeClass; ?>"><?php echo htmlspecialchars($notif['type']); ?></span>
                            </div>
                            <p class="card-message"><?php echo htmlspecialchars($notif['message']); ?></p>
                            <span class="card-date"><?php echo date('j mei Y', strtotime($notif['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>