# Aurora
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

                        $error_toevoegen = false;

<?php
// Database verbinding laden
require_once 'db.php';
// Nieuwe medewerker toevoegen
// $foutmelding_toevoegen = "";

// if (isset($_POST['toevoegen'])) {

//     $naam = $_POST['naam'];
//     $functie = $_POST['functie'];
//     $afdeling = $_POST['afdeling'];

//     $sql = "INSERT INTO medewerkers (naam, functie, afdeling)
//             VALUES ('$naam', '$functie', '$afdeling')";

//     try {

//         mysqli_query($conn, $sql);

//         header("Location: index.php?success=1");
//         exit();

//     } catch (Exception $e) {

//     header("Location: index.php?error=1");
//     exit();
// }
// }
$error_toevoegen = false;

if (isset($_POST['toevoegen'])) {

    try {

        mysqli_query($conn, $sql);

        header("Location: index.php?success=1");
        exit();

    } catch (Exception $e) {

        $error_toevoegen = true;
    }
}
// Medewerker succesvol toegevoegd, pagina opnieuw laden
// header("Location: index.php?success=1");
//    if (mysqli_query($conn, $sql)) {
//     header("Location: index.php?success=1");
//     exit();
// }


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
    $foutmelding =
        "De database is momenteel niet beschikbaar. Probeer later opnieuw.";
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
