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

    // NIEUWE FUNCTIE USER STORY 9: Wijzig type van 'Concept' naar 'Info' (versturen)
    public function sendNotification(int $id, string $newType = 'Info'): bool
    {
        if ($this->db === null) {
            return false;
        }

        $query = "UPDATE notifications 
                  SET type = :type 
                  WHERE id = :id";
                  
        $stmt = $this->db->prepare($query);          


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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

        // ACTIE 1: Er is op de knop "Melding Versturen" gedrukt
        if ($_POST['action'] === 'new_notification') {
            $title = trim($_POST['title']);
            $message = trim($_POST['message']);
            $type = 'Concept';

            if (!empty($title) && !empty($message)) {
                // Als de database vooraf al stuk was, sturen we DIRECT door naar de error pagina
                if ($systeemFout) {
                    header("Location: meldingen.php?error=db");
                    exit();
                }

                try {
                    $success = $notificationManager->createNotification($title, $message, $type);

                    if ($success) {
                        header("Location: meldingen.php?success=1");
                        exit();
                    } else {
                        // Als createNotification 'false' teruggeeft (omdat db null is)
                        header("Location: meldingen.php?error=db");
                        exit();
                    }
                } catch (Exception $e) {
                    // Als er tijdens het uitvoeren een database-fout komt (bijv. tabelnaam fout)
                    header("Location: meldingen.php?error=db");
                    exit();
                }
            }
        }


        // ACTIE 2: Er is op een prullenbak-knop gedrukt om te verwijderen
        if ($_POST['action'] === 'delete_notification' && isset($_POST['id']) && !$systeemFout) {
            $deleteId = intval($_POST['id']);
            $notificationManager->deleteNotification($deleteId);
            header("Location: meldingen.php"); // Ook netjes redirecten na verwijderen!
            exit();
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

        <h2 style="font-size: 28px; margin-bottom: 25px; font-family: 'Playfair Display', serif; color: #6366f1;">Mijn Opgestelde Concepten</h2>
        <div style="margin-bottom: 40px;">
            <?php
            $heeftConcepten = false;
            if (!$systeemFout && !empty($notifications)):
                foreach ($notifications as $notif):
                    if ($notif['type'] === 'Concept'):
                        $heeftConcepten = true;
            ?>
                        <div class="card-notification concept-item" style="border: 1px dashed #b4b6f9; margin-bottom: 25px; background: #fff; padding: 24px; border-radius: 12px; position: relative;">
                            <div class="card-body-content" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">

                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span class="badge" style="background-color: #eef2ff; color: #6366f1; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; font-family: 'Poppins', sans-serif;">Nog niet verzonden</span>
                                        <span style="font-size: 13px; color: #94a3b8; font-family: 'Poppins', sans-serif;">22 juni 2026</span>
                                    </div>

                                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 18px; margin: 4px 0 0 0; color: #1e293b;"><?php echo htmlspecialchars($notif['title']); ?></h3>

                                    <p class="card-message" style="margin: 2px 0 0 0; color: #64748b; font-size: 14px; font-family: 'Poppins', sans-serif;"><?php echo htmlspecialchars($notif['message']); ?></p>
                                </div>

                                <div style="display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
                                    <form action="meldingen.php" method="POST" style="margin: 0;">
                                        <input type="hidden" name="action" value="send_notification">
                                        <input type="hidden" name="id" value="<?php echo $notif['id']; ?>">
                                        <button type="submit" style="background-color: #6366f1; color: white; border: none; padding: 10px 24px; border-radius: 24px; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: 14px; cursor: pointer; transition: background 0.2s; shadow: 0 2px 4px rgba(99, 102, 241, 0.2);">Versturen</button>
                                    </form>

                                    <form action="meldingen.php" method="POST" onsubmit="return confirm('Weet je zeker dat je dit concept wilt verwijderen?');" style="margin: 0;">
                                        <input type="hidden" name="action" value="delete_notification">
                                        <input type="hidden" name="id" value="<?php echo $notif['id']; ?>">
                                        <button type="submit" style="background-color: white; color: #64748b; border: 1px solid #e2e8f0; padding: 10px 24px; border-radius: 24px; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: 14px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#334155';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">Verwijderen</button>
                                    </form>
                                </div>

                            </div>
                        </div>
                <?php
                    endif;
                endforeach;
            endif;

            if (!$heeftConcepten): ?>
                <p style="color: #94a3b8; font-style: italic;">Er zijn momenteel geen concepten opgesteld.</p>
            <?php endif; ?>
        </div>

        <hr style="border: 0; height: 1px; background: #e2e8f0; margin-bottom: 40px;">

        <div style="margin-bottom: 50px;">
            <h2 style="font-size: 28px; margin-bottom: 25px; font-family: 'Playfair Display', serif;">Recente Meldingen</h2>

            <?php if (!$systeemFout && !empty($notifications)): ?>
                <?php foreach ($notifications as $notif):
                    // Sla concepten hier over zodat ze alleen in de bovenste lijst staan!
                    if ($notif['type'] === 'Concept') {
                        continue;
                    }

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
            <h2>Nieuwe Melding Maken</h2>

            <?php if (isset($_POST['action']) && $_POST['action'] === 'new_notification' && !$systeemFout): ?>
                <div class="alert alert-success">
                    De nieuwe melding is toegevoegd en zichtbaar in het overzicht.
                </div>
            <?php endif; ?>

            <?php if ($systeemFout): ?>
                <div class="alert alert-danger">
                    De database is momenteel niet bereikbaar. Uw melding kon niet worden toegevoegd. Probeer het later opnieuw.
                </div>
            <?php endif; ?>

            <form action="meldingen.php" method="POST">
                <input type="hidden" name="action" value="new_notification">

                <div class="form-group">
                    <label for="title">Titel</label>
                    <input type="text" id="title" name="title" placeholder="Titel van de melding" required>
                </div>

                <div class="form-group">
                    <label for="message">Bericht</label>
                    <textarea id="message" name="message" rows="5" placeholder="Uw bericht..." required></textarea>
                </div>

                <button type="submit" class="btn-submit-purple">Toevoegen</button>
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