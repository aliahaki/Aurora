<?php

// ==========================================
// Database verbinding laden
// ==========================================

require_once 'db.php';

// ==========================================
// Controleer of een medewerker ID is meegegeven
// ==========================================

if (!isset($_GET['id'])) {

    header("Location: index.php?deleteError=1");
    exit();

}

// Medewerker ID ophalen
$id = (int) $_GET['id'];

// ==========================================
// Medewerker verwijderen
// ==========================================

$sql = "DELETE FROM medewerkers WHERE id = $id";

try {

    if (mysqli_query($conn, $sql)) {

        // Succesvol verwijderd
        header("Location: index.php?deleted=1");
        exit();

    } else {

        // Query mislukt
        header("Location: index.php?deleteError=1");
        exit();

    }

} catch (Exception $e) {

    // Database fout
    header("Location: index.php?deleteError=1");
    exit();

}