<?php
require_once 'config.php';
session_start(); // Start the session

// Default value for connect
$connect = false;

if (isset($_SESSION['username'])) {
    $connect = true;

    // Toggle edit mode if requested
    if (isset($_GET['toggle_edit'])) {
        $_SESSION['edit'] = $_SESSION['edit'] === 1 ? 0 : 1;

        // Mettre à jour la base de données
        try {
            $stmt = $pdo->prepare("UPDATE users SET edit = :edit WHERE username = :username");
            $stmt->bindParam(':edit', $_SESSION['edit'], PDO::PARAM_INT);
            $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
        }

        // Rafraîchir la page
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }

    // Reload session data from the database
    try {
        $stmt = $pdo->prepare("SELECT username, edit, perm FROM users WHERE username = :username");
        $stmt->bindParam(':username', $_SESSION['username']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update session variables with latest data
            $_SESSION['edit'] = $user['edit'];
            $_SESSION['perm'] = $user['perm'];
        } else {
            // If user is not found, destroy the session
            session_destroy();
            $connect = false;
        }
    } catch (PDOException $e) {
        // Handle errors (optional: log them)
        error_log("Database error: " . $e->getMessage());
        session_destroy();
        $connect = false;
    }
    
}


try {
    $stmt = $pdo->query("SELECT * FROM event_cards ORDER BY event_date ASC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des événements : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $title = trim($_POST['event-title']);
    $date = trim($_POST['event-date']);
    $category = trim($_POST['event-categ']);
    $location = trim($_POST['event-lieux']);
    $image_url = trim($_POST['event-img']);

    // Validation basique
    if (empty($title) || empty($location) || empty($image_url)) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!filter_var($image_url, FILTER_VALIDATE_URL)) {
        $error = "L'URL de l'image n'est pas valide.";
    } else {
        // Insertion dans la base de données
        try {
            $stmt = $pdo->prepare("INSERT INTO event_cards (category, title, image_url, event_date, location) VALUES ( :category, :title, :image_url, :date, :location)");
            $stmt->bindParam(':image_url', $image_url, PDO::PARAM_STR);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);            
            $stmt->bindParam(':location', $location, PDO::PARAM_STR);
            $stmt->execute();
            $success = "L'event a été ajoutée avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout de l'event : " . $e->getMessage();
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
    <title>National Progress Alliance</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://kit.fontawesome.com/d76525ec9b.js" crossorigin="anonymous"></script>

    <meta property="og:title" content="National Progress Alliance" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://npa.verenium.be/" />
    <meta property="og:image" content="https://npa.verenium.be/img/1129x500_npa" />
</head>
<body>

    <section class="container form-section">
        <button class="close-btn"><i class="fa-solid fa-plus"></i></button>
        <article class="form-container">
            <h1>+ Event</h1>
            <?php if (isset($success)): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php elseif (isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form action="" method="POST">
                <label for="event-title">Titre</label><br>
                <input type="text" id="event-title" name="event-title" required><br>

                <label for="event-lieux">Lieux</label><br>
                <input type="text" id="event-lieux" name="event-lieux" required><br>

                <label for="event-date">Date</label><br>
                <input type="datetime-local" id="event-date" name="event-date">

                <label for="event-categ">Catégorie</label><br>
                <input type="text" id="event-categ" name="event-categ">

                <label for="event-img">Image (URL)</label><br>
                <input type="text" id="event-img" name="event-img" required><br>

                <input class="submit-btn" type="submit" value="Publier">
            </form>
        </article>
    </section>

    <header>
        <nav>
            <?php 
                if ($connect && isset($_SESSION['edit'])) {
                    if ($_SESSION['edit'] === 1) {
                        echo "<a href='?toggle_edit=1'>Mode <strong>Edition</strong></a>";
                    } else {
                        echo "<a href='?toggle_edit=0'>Mode <strong>Edition</strong></a>";
                    }
                }
            ?>
            <a href="notre-programme">notre <strong>programme</strong></a>
            <a href="nos-evenements">nos <strong>évènements</strong></a>
            <a href="index" class="logo"><img src="img/logo.png" alt=""></a>
            <a href="elections">les <strong>élèctions</strong></a>
            <?php 
                if ($connect === true) {
                    echo "<a href=''> 🫡Hey <strong>". $_SESSION['username'] ."!</strong></a>";
                    echo "<a href='logout'>Se <strong>Deconnecter</strong></a>";
                } else {
                    echo "<a href='login'>Se <strong>Connecter</strong></a>";
                }
            ?>
        </nav>
    </header>
        <?php 
            if ($connect == true && $_SESSION['edit'] === 1) {
                echo "<span class='editor-mode'>Mode Edition Actif</span>";
            }
        ?>

    <section class="search-container">
        <span>A venir</span>
        <span class="searchbar">
            <input type="text" name="event-search" id="event-search" placeholder="RECHERCHER ...">
        </span>
    </section>

    <section id="event-results" class="event-container container">
        <?php foreach ($events as $event): ?>
            <div class="event-card">
                <span class="event-category"><?= htmlspecialchars($event['category']); ?></span>
                <h1 class="event-title"><?= htmlspecialchars($event['title']); ?></h1>
                <div class="event-img">
                    <img src="<?= htmlspecialchars($event['image_url']); ?>" alt="">
                </div>
                <div class="event-info">
                    <p><i class="fa-solid fa-calendar-days"></i> 
                        <?= date('l j F Y à H:i', strtotime($event['event_date'])); ?></p>
                    <p><i class="fa-solid fa-location-dot"></i> 
                        <?= htmlspecialchars($event['location']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <?php
            if ($connect == true && $_SESSION['edit'] === 1) {
                echo '<div class="add event-card"><button class="add-btn"><i class="fa-solid fa-plus"></i></button></div>';
            }
        ?>
    </section>

    <script>
        document.getElementById('event-search').addEventListener('input', function () {
            const query = this.value;

            // Effectuer une requête Ajax
            fetch('search_events.php?q=' + encodeURIComponent(query))
                .then(response => response.text())
                .then(html => {
                    document.getElementById('event-results').innerHTML = html;
                })
                .catch(error => console.error('Erreur:', error));
        });
    </script>

    <footer>
        <nav>
            <a href="">le <strong>parti</strong></a>
            <a href="">notre <strong>programme</strong></a>
            <a href=""><img src="logo.png" alt=""></a>
            <a href="">nos <strong>évènements</strong></a>
            <a href="">les <strong>élèctions</strong></a>
        </nav>
        <span>&copy Lucass - 2024</span>
    </footer>
</body>
<script src="js/form.js"></script>
</html>