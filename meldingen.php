<?php
// ==========================================================================
// 1. DATABASE KLASSE (OOP)
// Dit onderdeel regelt de verbinding met jouw MySQL (WAMP) database.
// ==========================================================================
class Database
{
    // PAS HIER DE NAAM AAN VOOR JE TEST (bv. 'aurora_theater_FOUT')
    private string $host = '127.0.0.1';
    private string $dbname = 'aurora_theater';
    private string $username = 'root';
    private string $password = '';
    public ?PDO $conn = null; // Hierin slaan we de actieve verbinding op

    public function __construct()
    {
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
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

        $created_at = date('Y-m-d');

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

    // FUNCTIE: Wijzig type van 'Concept' naar 'Info' (versturen)
    public function sendNotification(int $id, string $newType = 'Info'): bool
    {
        if ($this->db === null) {
            return false;
        }

        $query = "UPDATE notifications 
                  SET type = :type 
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':type' => $newType,
            ':id' => $id
        ]);
    }

    // FUNCTIE: Verwijder een melding uit de database met het ID
    public function deleteNotification(int $id): bool
    {
        if ($this->db === null) {
            return false;
        }

        $query = "DELETE FROM notifications 
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $id
        ]);
    }

    // ==========================================================================
    // HIER STUURDEN WE DE FEEDBACK FUNCTIES HEEN BINNEN DE KLASSE!
    // ==========================================================================

    // FUNCTIE: Sla ontvangen feedback op in de MySQL database
    public function createFeedback(string $name, string $message): bool
    {
        if ($this->db === null) {
            return false;
        }

        $query = "INSERT INTO feedback (name, message, created_at) 
                  VALUES (:name, :message, NOW())";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $name,
            ':message' => $message
        ]);
    }

    // FUNCTIE: Haal alle ingezonden feedback op (Nieuwste eerst)
    public function getAllFeedback(): array
    {
        if ($this->db === null) {
            return [];
        }

        $query = "SELECT id, name, message, created_at 
                  FROM feedback 
                  ORDER BY created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ==========================================================================
// 3. APPLICATIE LOGICA (HET WERKBOEK VAN PHP)
// Hier verwerken we alle formulieren (Meldingen én Feedback).
// ==========================================================================
$systeemFout = false;
$feedbackFout = false; // Nieuwe variabele voor feedback-fouten
$notifications = [];
$feedbackLijst = [];

try {
    // DIRECTE VERBINDING (VEILIGHEIDS-CHECK): We verbinden rechtstreeks met aurora_theater
    $directDb = new PDO("mysql:host=localhost;dbname=aurora_theater;charset=utf8", "root", "");
    $directDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // We maken de manager aan met deze directe, werkende verbinding
    $notificationManager = new NotificationManager($directDb);
} catch (Exception $e) {
    // Mocht dit toch falen, val dan terug op je oude Database-klasse
    try {
        $database = new Database();
        $notificationManager = new NotificationManager($database->conn);
        $systeemFout = ($database->conn === null);
    } catch (Exception $ex) {
        $systeemFout = true;
    }
}

try {
    // Controleren of er een formulier (POST-request) wordt verstuurd
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

        // ACTIE 1: Nieuwe melding aanmaken (Vorige Sprint)
        if ($_POST['action'] === 'new_notification') {
            $title = trim($_POST['title']);
            $message = trim($_POST['message']);
            $type = 'Concept';

            if (!empty($title) && !empty($message)) {
                if ($systeemFout) {
                    header("Location: meldingen.php?error=db");
                    exit();
                }
                $success = $notificationManager->createNotification($title, $message, $type);
                if ($success) {
                    header("Location: meldingen.php?success=1");
                    exit();
                } else {
                    header("Location: School_Project/Aurora/meldingen.php?error=db");
                    exit();
                }
            }
        }

        // ACTIE: Bestaand concept definitief versturen (Nieuws van maken)
        if ($_POST['action'] === 'send_existing_concept' && isset($_POST['id'])) {

            // Check of de database al als fout is gemarkeerd bij Stap 3
            if (isset($systeemFout) && $systeemFout === true) {
                header("Location: meldingen.php?error=db_versturen_failed");
                exit();
            }

            try {
                // We halen de database-verbinding rechtstreeks uit de al bestaande manager!
                // Mocht $notificationManager niet werken, gebruiken we $directDb die bij stap 3 is gemaakt.
                $dbVerbinding = $directDb;

                if (!$dbVerbinding) {
                    header("Location: meldingen.php?error=db_versturen_failed");
                    exit();
                }

                $conceptId = intval($_POST['id']);
                $query = "UPDATE notifications SET type = 'Nieuws', created_at = NOW() WHERE id = :id";

                $stmt = $dbVerbinding->prepare($query);
                $success = $stmt->execute([':id' => $conceptId]);

                if ($success) {
                    header("Location: meldingen.php?success=verstuurd");
                    exit();
                } else {
                    header("Location: meldingen.php?error=db_versturen_failed");
                    exit();
                }
            } catch (Exception $e) {
                header("Location: meldingen.php?error=db_versturen_failed");
                exit();
            }
        }
        // ACTIE 2: Melding verwijderen
        if ($_POST['action'] === 'delete_notification' && isset($_POST['id']) && !$systeemFout) {
            $deleteId = intval($_POST['id']);

            if ($systeemFout) {
                header("Location: meldingen.php?error=db");
                exit();
            }

            $notificationManager->deleteNotification($deleteId);
            header("Location: meldingen.php");
            exit();
        }

        // ACTIE 3: ER WORDT FEEDBACK INGESTUURD!
        if ($_POST['action'] === 'submit_feedback') {
            if ($systeemFout) {
                header("Location: meldingen.php?error=feedback_db_error#ingezonden-feedback");
                exit();
            }

            $name = !empty($_POST['name']) ? trim($_POST['name']) : 'Anoniem';
            $message = !empty($_POST['message']) ? trim($_POST['message']) : '';

            if (!empty($message)) {
                try {
                    $success = $notificationManager->createFeedback($name, $message);

                    if ($success) {
                        header("Location: meldingen.php?success=feedback_saved#ingezonden-feedback");
                        exit();
                    } else {
                        header("Location: meldingen.php?error=feedback_db_error#ingezonden-feedback");
                        exit();
                    }
                } catch (Exception $e) {
                    header("Location: meldingen.php?error=feedback_db_error#ingezonden-feedback");
                    exit();
                }
            }
        }
    }

    // Gegevens ophalen voor de overzichten
    if (!$systeemFout) {
        // Probeer eerst de meldingen op te halen
        try {
            $notifications = $notificationManager->getAllNotifications();
        } catch (Exception $ne) {
            // Fout bij meldingen tabel opvangen
        }

        // Probeer apart de feedback op te halen (zodat een crash hier niet de meldingen blokkeert!)
        try {
            $feedbackLijst = $notificationManager->getAllFeedback();
        } catch (Exception $fe) {
            // HIER GAAT HET MIS ALS DE TABEL HERNOEMD IS!
            $feedbackFout = true;
        }
    }
} catch (Exception $e) {
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

        <?php if (isset($_GET['error']) && $_GET['error'] === 'db_versturen_failed'): ?>
            <div style="background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: 16px 24px; border-radius: 12px; margin-bottom: 25px; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                De melding kon niet worden verstuurd omdat de database niet beschikbaar is. Probeer het later opnieuw.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'verstuurd'): ?>
            <div id="success-alert" style="background-color: #dcfce7; color: #166534; border: 1px solid #86efac; padding: 16px 24px; border-radius: 12px; margin-bottom: 25px; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; opacity: 1; transition: opacity 0.5s ease;">
                <i class="fa-solid fa-circle-check"></i>
                De melding is succesvol verstuurd naar de bezoekers.
            </div>
        <?php endif; ?>


        <h2 style="font-size: 28px; margin-bottom: 25px; font-family: 'Playfair Display', serif; color: #6366f1;">Mijn Opgestelde Concepten</h2>
        <div style="margin-bottom: 40px;">
            <?php
            $heeftConcepten = false;
            if (!$systeemFout && !empty($notifications)):
                foreach ($notifications as $notif):
                    if ($notif['type'] === 'Concept'):
                        $heeftConcepten = true;

                        // Vaste Figma testdatum of database datum
                        $conceptDatum = '5 juli 2026';
                        if (isset($notif['created_at'])) {
                            $timestamp = strtotime($notif['created_at']);
                            $maanden = ['January' => 'januari', 'February' => 'februari', 'March' => 'maart', 'April' => 'april', 'May' => 'mei', 'June' => 'juni', 'July' => 'juli', 'August' => 'augustus', 'September' => 'september', 'October' => 'oktober', 'November' => 'november', 'December' => 'december'];
                            $engelseMaand = date('F', $timestamp);
                            $conceptDatum = date('j ', $timestamp) . $maanden[$engelseMaand] . date(' Y', $timestamp);
                        }
            ?>
                        <div style="border: 1px dashed #c7d2fe; border-radius: 16px; padding: 24px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; background-color: #ffffff; font-family: 'Poppins', sans-serif;">

                            <div style="display: flex; flex-direction: column; gap: 12px; flex-grow: 1;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span style="background-color: #eedffc; color: #6366f1; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">Nog niet verzonden</span>
                                    <span style="font-size: 13px; color: #94a3b8;"><?php echo $conceptDatum; ?></span>
                                </div>

                                <div style="margin-top: 4px;">
                                    <h3 style="font-weight: 700; font-size: 18px; margin: 0; color: #1e293b;"><?php echo htmlspecialchars($notif['title']); ?></h3>
                                    <p style="margin: 6px 0 0 0; color: #64748b; font-size: 14px;"><?php echo htmlspecialchars($notif['message']); ?></p>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 12px; flex-shrink: 0; margin-left: 20px;">
                                <form action="meldingen.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="send_existing_concept">
                                    <input type="hidden" name="id" value="<?php echo $notif['id']; ?>">
                                    <button type="submit" style="background-color: #6366f1; color: white; border: none; padding: 10px 24px; border-radius: 30px; font-weight: 600; cursor: pointer; font-size: 14px; font-family: 'Poppins', sans-serif; transition: background 0.2s; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">
                                        Versturen
                                    </button>
                                </form>

                                <form action="meldingen.php" method="POST" onsubmit="return confirm('Weet je zeker dat je dit concept wilt verwijderen?');" style="margin: 0;">
                                    <input type="hidden" name="action" value="delete_notification">
                                    <input type="hidden" name="id" value="<?php echo $notif['id']; ?>">
                                    <button type="submit" style="background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 10px 24px; border-radius: 30px; font-weight: 600; cursor: pointer; font-size: 14px; font-family: 'Poppins', sans-serif; transition: all 0.2s;">
                                        Verwijderen
                                    </button>
                                </form>
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

        <div class="feedback-card">
            <h2>Feedback</h2>
            <p>We waarderen uw feedback! Laat ons weten wat u van onze service vindt.</p>
            <?php if (isset($_GET['success']) && $_GET['success'] === 'feedback_saved'): ?>
                <div id="feedback-success-alert" style="background-color: #dcfce7; color: #166534; border: 1px solid #86efac; padding: 16px 24px; border-radius: 12px; margin-bottom: 25px; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; opacity: 1; transition: opacity 0.5s ease;">
                    <i class="fa-solid fa-circle-check"></i>
                    Je feedback is succesvol verzonden! Bedankt voor je bericht.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'feedback_db_error'): ?>
                <?php if (!$systeemFout): ?>
                    <script>
                        window.location.href = 'meldingen.php#ingezonden-feedback';
                    </script>
                    <?php exit(); ?>
                <?php endif; ?>
                <?php if ((isset($feedbackFout) && $feedbackFout) || $systeemFout || (isset($_GET['error']) && $_GET['error'] === 'feedback_db_error')): ?>
                    <div style="background-color: #ffeeef; color: #af233a; border: 1px solid #fed7da; padding: 16px 24px; border-radius: 12px; margin-bottom: 25px; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500;">
                        De database is momenteel niet bereikbaar. Uw feedback kon niet worden verwerkt. Probeer het later opnieuw.
                    </div>

                <?php endif; ?>
            <?php endif; ?>

            <div class="tab-buttons">
                <button type="button" id="tab-versturen" class="btn-tab active">
                    <i class="fas fa-paper-plane"></i> Feedback Versturen
                </button>
                <button type="button" id="tab-ontvangen" class="btn-tab">
                    <i class="fas fa-inbox"></i> Ontvangen Feedback
                    <span class="badge"><?php echo count($feedbackLijst); ?></span>
                </button>
            </div>

            <div id="feedback-form-container" class="tab-content">
                <form action="meldingen.php" method="POST">
                    <input type="hidden" name="action" value="submit_feedback">

                    <div class="form-group">
                        <label for="name">Naam (optioneel)</label>
                        <input type="text" id="name" name="name" placeholder="Uw naam of anoniem laten">
                    </div>

                    <div class="form-group">
                        <label for="message">Bericht</label>
                        <textarea id="message" name="message" placeholder="Deel uw feedback met ons..." required></textarea>
                    </div>

                    <button type="submit" class="btn-submit">Feedback Verzenden</button>
                </form>
            </div>

            <div id="feedback-lijst-container" class="tab-content hidden">
                <?php if (empty($feedbackLijst)): ?>
                    <p class="empty-text">Er is momenteel nog geen feedback ontvangen.</p>
                <?php else: ?>
                    <div class="feedback-grid">
                        <?php foreach ($feedbackLijst as $fb): ?>
                            <div class="feedback-item">
                                <div class="feedback-avatar">
                                    <?php echo strtoupper(substr(htmlspecialchars($fb['name']), 0, 1)); ?>
                                </div>
                                <div class="feedback-body">
                                    <div class="feedback-header">
                                        <strong><?php echo htmlspecialchars($fb['name']); ?></strong>
                                        <div class="feedback-date" style="color: #9ca3af; font-size: 13px;">
                                            <?php
                                            // We gebruiken $fb['created_at'] omdat jouw variabele $fb heet!
                                            $date = new DateTime($fb['created_at']);
                                            $maanden = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
                                            echo $date->format('j ') . $maanden[$date->format('n') - 1] . $date->format(' Y');
                                            ?>
                                        </div>
                                    </div>
                                    <p><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
                                </div>
                            </div> <?php endforeach; ?>
                    </div> <?php endif; ?>
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

    <script>
        // Zodra de pagina is geladen, halen we de 'error' of 'success' parameter stilletjes weg uit de URL
        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('error');
            url.searchParams.delete('success');
            window.history.replaceState({
                path: url.href
            }, '', url.href);
        }
    </script>

    <script src="js/main.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Zoek de succesbalk op via de juiste ID
            const alertBox = document.getElementById("success-alert");

            if (alertBox) {
                // Wacht 3 seconden (3000 milliseconden)
                setTimeout(function() {
                    // Maak de balk onzichtbaar (vervaag-effect)
                    alertBox.style.opacity = "0";

                    // Wacht nog 500ms totdat de animatie klaar is, en haal hem dan helemaal weg
                    setTimeout(function() {
                        alertBox.style.display = "none";
                    }, 500);
                }, 3000);
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Check voor de melding van versturen meldingen
            const alertBox = document.getElementById("success-alert");
            if (alertBox) {
                setTimeout(function() {
                    alertBox.style.opacity = "0";
                    setTimeout(function() {
                        alertBox.style.display = "none";
                    }, 500);
                }, 3000);
            }

            // 2. Check voor de nieuwe feedback succesbalk
            const feedbackAlertBox = document.getElementById("feedback-success-alert");
            if (feedbackAlertBox) {
                setTimeout(function() {
                    feedbackAlertBox.style.opacity = "0";
                    setTimeout(function() {
                        feedbackAlertBox.style.display = "none";
                    }, 500);
                }, 3000);
            }
        });
    </script>
</body>

</html>