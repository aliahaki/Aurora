<?php
// ==========================================================================
// 1. DATABASE KLASSE (OOP)
// Dit onderdeel regelt de verbinding met jouw MySQL (WAMP) database.
// ==========================================================================
class Database
{
    private string $host = '127.0.0.1';
    private string $dbname = 'aurora_theater';
    private string $username = 'root';
    private string $password = '';
    public ?PDO $conn = null; // Hierin slaan we de active verbinding op

    // De constructor start automatisch zodra we 'new Database()' aanroepen
    public function __construct()
    {
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8", $this->username, $this->password);
            // CRUCIAAL: Dit zorgt ervoor dat PDO échte fouten (Exceptions) gooit als phpMyAdmin faalt
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Als de verbinding mislukt, blijft $conn leeg (null)
            $this->conn = null;
        }
    }
}

// ==========================================================================
// 2. MELDINGEN MANAGER KLASSE (OOP)
// Deze klasse regelt alles met de meldingen: ophalen, toevoegen en verwijderen.
// ==========================================================================
class NotificationManager
{
    private ?PDO $db;

    // We geven de database-verbinding mee via de constructor
    public function __construct(?PDO $databaseConnection)
    {
        $this->db = $databaseConnection;
    }

    // FUNCTIE: Haal alle meldingen op uit de database
    public function getAllNotifications(): array
    {
        if ($this->db === null) {
            return [];
        }

        // SQL-query met de komma's netjes aan het begin van de regel
        $query = "SELECT id
                       , title
                       , message
                       , type
                       , created_at 
                  FROM notifications 
                  ORDER BY created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // FUNCTIE: Sla een nieuwe melding op in de database
    public function createNotification(string $title, string $message, string $type): bool
    {
        if ($this->db === null) {
            return false;
        }

        $created_at = date('Y-m-d'); // Pakt de datum van vandaag

        // SQL-query om data in tevoegen met komma's aan het begin
        $query = "INSERT INTO notifications (title
                                           , message
                                           , type
                                           , created_at) 
                  VALUES (:title
                        , :message
                        , :type
                        , :created_at)";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':title' => $title,
            ':message' => $message,
            ':type' => $type,
            ':created_at' => $created_at
        ]);
    }

    // FUNCTIE: Verwijder een melding uit de database met het ID
    public function deleteNotification(int $id): bool
    {
        if ($this->db === null) {
            return false;
        }

        // SQL-query om 1 specifieke rij te wissen
        $query = "DELETE FROM notifications 
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $id
        ]);
    }
}

// ==========================================================================
// 3. APPLICATIE LOGICA (HETWERKBOEK VAN PHP)
// Hier verwerken we de formulieren als er op een knop wordt gedrukt.
// ==========================================================================
$database = new Database();
$notificationManager = new NotificationManager($database->conn);

// Als $conn null is, zetten we de systeemfout op true
$systeemFout = ($database->conn === null);
$notifications = [];

// HIER VANGEN WE DE FOUT OP ALS DE TABELNAAM IS VERANDERD IN PHPMYADMIN
try {
    // Controleren of er een formulier (POST-request) wordt verstuurd
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && !$systeemFout) {

        // ACTIE 1: Er is op de knop "Melding Versturen" gedrukt
        if ($_POST['action'] === 'new_notification') {
            $title = trim($_POST['title']);
            $message = trim($_POST['message']);
            $type = 'Nieuws';

            if (!empty($title) && !empty($message)) {
                $notificationManager->createNotification($title, $message, $type);
            }
        }

        // ACTIE 2: Er is op een prullenbak-knop gedrukt om te verwijderen
        if ($_POST['action'] === 'delete_notification' && isset($_POST['id'])) {
            $deleteId = intval($_POST['id']); // Maak er voor de veiligheid een heel getal (integer) van
            $notificationManager->deleteNotification($deleteId);
        }
    }

    // Als er geen database-fout is, halen we direct de nieuwste lijst met meldingen op
    if (!$systeemFout) {
        $notifications = $notificationManager->getAllNotifications();
    }
} catch (PDOException $e) {
    // Als phpMyAdmin zegt "Tabel bestaat niet", activeren we hier de systeemfout!
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
        Meldingen kunnen momenteel niet worden geladen. Probeer het later opnieuw.
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

                    <a href="./medewerker/overzicht/index.php" style="text-decoration: none;">
                        <button class="btn-employee" id="btn-employee" aria-label="Medewerker">
                            💼 Medewerker
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
                    // Bepaal de juiste CSS-klas en FontAwesome-icoon op basis van het type melding
                    $typeClass = strtolower($notif['type']);
                    if ($typeClass == 'waarschuwing') {
                        $typeClass = 'warning';
                        $icon = 'fa-bell';
                        $mooieDatum = '12 juli 2026'; // Vaste testdatum in de toekomst
                    } elseif ($typeClass == 'nieuws') {
                        $typeClass = 'news';
                        $icon = 'fa-volume-high';
                        $mooieDatum = '2 augustus 2026'; // Vaste testdatum in de toekomst
                    } else {
                        $typeClass = 'info';
                        $icon = 'fa-circle-info';
                        $mooieDatum = '5 juli 2026'; // Vaste testdatum in de toekomst
                    }

                    // Als de melding VANDAAG is gemaakt via het formulier, toon dan de echte datum van nu
                    if (isset($notif['created_at']) && date('Y-m-d') === date('Y-m-d', strtotime($notif['created_at']))) {
                        $timestamp = strtotime($notif['created_at']);
                        $maanden = [
                            'January' => 'januari',
                            'February' => 'februari',
                            'March' => 'maart',
                            'April' => 'april',
                            'May' => 'mei',
                            'June' => 'juni',
                            'July' => 'juli',
                            'August' => 'augustus',
                            'September' => 'september',
                            'October' => 'oktober',
                            'November' => 'november',
                            'December' => 'december'
                        ];
                        $engelseMaand = date('F', $timestamp);
                        $mooieDatum = date('j ', $timestamp) . $maanden[$engelseMaand] . date(' Y', $timestamp);
                    }
                ?>
                    <div class="card-notification <?php echo $typeClass; ?>">
                        <div class="card-icon-wrapper <?php echo $typeClass; ?>">
                            <i class="fa-regular <?php echo $icon; ?>"></i>
                        </div>
                        <div class="card-body-content">
                            <div class="card-title-row">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 18px; margin: 0; color: var(--text-dark);"><?php echo htmlspecialchars($notif['title']); ?></h3>
                                    <span class="badge <?php echo $typeClass; ?>"><?php echo htmlspecialchars($notif['type']); ?></span>
                                </div>

                                <form action="meldingen.php" method="POST" onsubmit="return confirm('Weet je zeker dat je deze melding wilt verwijderen?');" style="margin: 0;">
                                    <input type="hidden" name="action" value="delete_notification">
                                    <input type="hidden" name="id" value="<?php echo $notif['id']; ?>">
                                    <button type="submit" style="background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 16px;" aria-label="Verwijderen">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                            <p class="card-message"><?php echo htmlspecialchars($notif['message']); ?></p>
                            <span class="card-date"><?php echo $mooieDatum; ?></span>
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

                <button type="submit" class="btn-submit-purple" <?php echo $systeemFout ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>Melding Versturen</button>
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