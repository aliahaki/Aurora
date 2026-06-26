<?php
// Database verbinding - simpel en duidelijk

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "aurora_user_db";

// Maak verbinding
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check of het werkt
if (!$conn) {
    die("Fout: Kan niet verbinden met database - " . mysqli_connect_error());
}
?>