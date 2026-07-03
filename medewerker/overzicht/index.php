<?php
// Database verbinding laden
require_once 'db.php';
// Nieuwe medewerker toevoegen
$error_toevoegen = false;

if (isset($_POST['toevoegen'])) {

    $naam = $_POST['naam'];
    $functie = $_POST['functie'];
    $afdeling = $_POST['afdeling'];
    if (empty($naam) || empty($functie) || empty($afdeling)) {
    $error_toevoegen = true;
}

    $sql = "INSERT INTO medewerkers (naam, functie, afdeling)
            VALUES ('$naam', '$functie', '$afdeling')";

    try {

        mysqli_query($conn, $sql);

        header("Location: index.php?success=1");
        exit();

    } catch (Exception $e) {

    header("Location: index.php?error=1");
    exit();
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
    $sql = "SELECT * FROM medewerkers
            WHERE naam LIKE '%$zoekterm%'
            OR functie LIKE '%$zoekterm%'
            ORDER BY naam ASC";
} else {
    $sql = "SELECT * FROM medewerkers
            ORDER BY naam ASC";
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

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Theater - Medewerkers</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigatie balk -->
<header>
    <nav class="navbar">
        <div class="logo">
            <span class="logo-icon">★</span> Aurora Theater
        </div>

        <!-- Hamburger menu knop -->
        <button class="menu-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <div class="nav-menu" id="nav-menu">
                <ul class="nav-links">
                    <li><a href="../../index.php" class="active">Home</a></li>
                    <li><a href="#">Informatie</a></li>
                    <li><a href="../../meldingen.php">Meldingen</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
                <div class="nav-buttons">
                    <a href="../../meldingen.php" style="text-decoration: none;">
                        <button class="btn-notifications" id="btn-alerts" aria-label="Meldingen">
                            🔔 Meldingen
                        </button>
                    </a>
                    <a href="../../medewerker/overzicht/index.php" style="text-decoration: none;">
    <button class="btn-employee" id="btn-employee">
        💼 Medewerker
    </button>
</a>
            </div>
        </div>
    </nav>
</header>

<!-- Hoofdinhoud -->
<div class="container">
    <?php if (isset($_GET['success'])): ?>
<div class="toast-success" id="toast">
    ✅ Medewerker succesvol toegevoegd!
</div>
<?php endif; ?>
<?php if(isset($_GET['error'])): ?>
<div class="toast-error">
    ❌ Database niet beschikbaar. Probeer later opnieuw.
</div>
<?php endif; ?>
    <!-- Titel -->
    <div class="header">
        <h1><span class="logo-icon">A</span> Aurora Theater</h1>
        <p>Overzicht medewerkers</p>
    </div>
   <!-- FOUTMELDING (als database niet werkt) -->
<?php if (!$db_beschikbaar && !isset($_GET['error'])): ?>
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-database"></i>
            </div>
            <h2>Database niet beschikbaar</h2>
            <p><?php echo $foutmelding; ?></p>
            <button class="btn-retry" onclick="window.location.href='index.php'">
    <i class="fas fa-sync-alt"></i> Opnieuw proberen
</button>
        </div>
    
    <!-- DATABASE WERKT -->
    <?php else: ?>
        
        <!-- Zoekbalk -->
        <div class="zoek-section">
            <form method="GET" class="zoek-form">
                <i class="fas fa-search"></i>
                <input type="text" name="zoek" placeholder="Zoek op naam of functie..." value="<?php echo htmlspecialchars($zoekterm); ?>">
                <button type="submit">Zoeken</button>
                <?php if ($zoekterm != ""): ?>
                    <a href="index.php" class="wis-knop"><i class="fas fa-times"></i> Wis</a>
                <?php endif; ?>
            </form>
            <!-- Knop nieuwe medewerker -->
            <button id="openModal" class="btn-add">
        ➕ Toevoegen
    </button>
        </div>
      
    
        <!-- Tabel met medewerkers -->
        <div class="tabel-container">
            <?php if ($totaal > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Naam</th>
                            <th>Functie</th>
                            <th>Afdeling</th>
                            <!--Actieknoppen-->
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($rij = mysqli_fetch_assoc($resultaat)): ?>
                            <tr>
                                <td><?php echo $rij['id']; ?></td>
                                <td class="naam-cell">
                                    <div class="avatar-small"><?php echo strtoupper(substr($rij['naam'], 0, 1)); ?></div>
                                    <strong><?php echo htmlspecialchars($rij['naam']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($rij['functie']); ?></td>
                                <td><span class="afdeling-badge"><?php echo htmlspecialchars($rij['afdeling']); ?></span></td>
                                                        <td>
    <!-- Bewerken knop -->
    <button type="button" class="btn-edit">
    ✏️ Wijzigen
</button>

    <!-- Verwijderen knop -->
   <button
    type="button"
    class="btn-delete"
    data-id="<?php echo $rij['id']; ?>">

    🗑️ Verwijderen

</button>
</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="geen-data">
                    <i class="fas fa-user-slash"></i>
                    <p>Geen medewerkers gevonden</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Totaal aantal -->
        <div class="stats">
        <p><i class="fas fa-users"></i> Totaal medewerkers:
            <strong><?php echo $totaal; ?></strong>
        </p>
    </div>
       
    <?php endif; ?>
</div>
<!-- Footer -->
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


<!-- Modal Nieuwe Medewerker -->
<div id="employeeModal" class="modal">

    <div class="modal-content">

    <!-- Sluitknop -->
<span id="closeModal" class="close">&times;</span>

        <h2>Nieuwe medewerker toevoegen</h2>
        
         <?php if (!empty($foutmelding_toevoegen)): ?>
    <div class="error-banner active">
        <?php echo $foutmelding_toevoegen; ?>
    </div>
<?php endif; ?>

        <form method="POST">

            <!-- Naam -->
            <input
                type="text"
                name="naam"
                placeholder="Volledige naam"
                required>
            
            <!-- Functie -->
            <input
                type="text"
                name="functie"
                placeholder="Functie"
                required>
            
            <!-- Afdeling -->
            <select name="afdeling" required>
    <option value="">Kies afdeling</option>
    <option value="Kassa">Kassa</option>
    <option value="Techniek">Techniek</option>
    <option value="Marketing">Marketing</option>
    <option value="Administratie">Administratie</option>
</select>
        

            <!-- Opslaan -->
            <button type="submit" name="toevoegen">
                Toevoegen
            </button>
            <button type="button" id="cancelModal">
    Annuleren
</button>

        </form>

    </div>

</div>
    <!-- Bewerken Modal -->
<div id="editModal" class="modal">

    <div class="modal-content">

        <!-- Sluitknop -->
        <span class="close-edit">&times;</span>

        <h2>Medewerker Bewerken</h2>
        <form>

    <!-- Naam -->
    <label>Volledige naam</label>

    <input
        type="text"
        placeholder="Naam">

    <!-- Functie -->
    <label>Functie</label>

    <input
        type="text"
        placeholder="Functie">

    <!-- Afdeling -->
    <label>Afdeling</label>

    <select>

        <option>Kassa</option>
        <option>Techniek</option>
        <option>Marketing</option>

    </select>

    <!-- Knoppen -->
    <div class="modal-buttons">

    <button
        type="button"
        class="btn-cancel">

        Annuleren

    </button>

    <button
        type="submit"
        class="btn-save">

        Opslaan

    </button>

</div>

</form>

    </div>
    </div>
    <!-- ==========================================
     Verwijder medewerker modal
========================================== -->

<div id="deleteModal" class="modal">

    <div class="modal-content delete-modal">

        <!-- Sluitknop -->
        <span class="close-delete">&times;</span>

        <!-- Waarschuwing -->
        <div class="delete-icon">

            🗑️

        </div>

        <h2>Medewerker verwijderen</h2>

        <p>

            Weet je zeker dat je deze medewerker wilt verwijderen?

        </p>

        <div class="modal-buttons">

            <button
                type="button"
                class="btn-cancel-delete">

                Annuleren

            </button>

            <button
                type="button"
                class="btn-confirm-delete">

                Verwijderen

            </button>

        </div>

    </div>

</div>
<!-- Javascript voor hamburger menu -->
<script>
    // Hamburger menu
    const menuToggle = document.getElementById('mobile-menu');
    const navMenu = document.getElementById('nav-menu');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }

    // Modal
    const openModal = document.getElementById('openModal');
    const closeModal = document.getElementById('closeModal');
    const modal = document.getElementById('employeeModal');
    // Sluiten ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        modal.style.display = 'none';
    }
});
    if (openModal) {
        openModal.addEventListener('click', () => {
            modal.style.display = 'flex';
        });
    }

    if (closeModal) {
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    const toast = document.getElementById('toast');

if (toast) {
    setTimeout(() => {
        toast.classList.add('fade-out');
    }, 3000);

    setTimeout(() => {
        toast.remove();
    }, 3500);
}
const cancelModal = document.getElementById('cancelModal');

if (cancelModal) {
    cancelModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });
}
if (window.location.search.includes('success=1') ||
    window.location.search.includes('error=1')) {

    const url = new URL(window.location);

    url.searchParams.delete('success');
    url.searchParams.delete('error');

    window.history.replaceState({}, '', url);
}
// ================================
// Bewerken modal openen/sluiten
// ================================

// Alle wijzigknoppen
const editButtons = document.querySelectorAll('.btn-edit');

// Bewerken modal
const editModal = document.getElementById('editModal');

// Sluitknop
const closeEdit = document.querySelector('.close-edit');

// Open modal
editButtons.forEach(button => {

    button.addEventListener('click', () => {

        editModal.classList.add("show");

    });

});

// Sluiten via kruisje
closeEdit.addEventListener('click', () => {

    editModal.classList.remove("show");

});

// Sluiten buiten het venster
window.addEventListener('click', (e) => {

    if (e.target === editModal) {

        editModal.classList.remove("show");

    }

});
/// =========================================
// Verwijder modal
// =========================================

// Geselecteerde medewerker
let medewerkerId = null;

// Alle verwijder knoppen
const deleteButtons =
document.querySelectorAll(".btn-delete");

// Modal
const deleteModal =
document.getElementById("deleteModal");

// Sluitknop
const closeDelete =
document.querySelector(".close-delete");

// Openen
deleteButtons.forEach(button => {

    button.addEventListener("click", () => {

        medewerkerId = button.dataset.id;

        deleteModal.classList.add("show");

    });

});
// =========================================
// Sluitknop
// =========================================

if (closeDelete) {

    closeDelete.addEventListener("click", () => {

        deleteModal.classList.remove("show");

    });

}

// =========================================
// Annuleren knop
// =========================================

const cancelDelete =
document.querySelector(".btn-cancel-delete");

if (cancelDelete) {

    cancelDelete.addEventListener("click", () => {

        deleteModal.classList.remove("show");

    });

}

// =========================================
// Buiten de modal klikken
// =========================================

window.addEventListener("click", (e) => {

    if (e.target === deleteModal) {

        deleteModal.classList.remove("show");

    }

});

// =========================================
// Verwijderen bevestigen
// =========================================

const confirmDelete =
document.querySelector(".btn-confirm-delete");

if (confirmDelete) {

    confirmDelete.addEventListener("click", () => {

        if (medewerkerId !== null) {

            window.location =
                "verwijderen.php?id=" + medewerkerId;

        }

    });

}

</script>
</body>
</html>

<?php
if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>