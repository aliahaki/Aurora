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
