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

// Requête SQL pour récupérer les résultats des candidats triés par score décroissant
$query = "SELECT id, name, img_url, score FROM candidat_results ORDER BY score DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();

// Récupérer les résultats dans un tableau
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <meta name="theme-color" content="#CE223B">
    <!-- HTML Meta Tags -->
    <title>National Progress Alliance</title>
    <meta name="description" content="">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="https://npa.verenium.be">
    <meta property="og:type" content="website">
    <meta property="og:title" content="National Progress Alliance">
    <meta property="og:description" content="Parti Politique - Droite Républicaine - Présidé par A. Moore">
    <meta property="og:image" content="https://opengraph.b-cdn.net/production/images/43d6c5ae-10a4-4dc4-b536-310a5ce42cbf.png?token=I6U77DgD7UxGQeHPSNix-952LqUJuWeW2jAx3uJIMOc&height=675&width=1200&expires=33271327511">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta property="twitter:domain" content="npa.verenium.be">
    <meta property="twitter:url" content="https://npa.verenium.be">
    <meta name="twitter:title" content="National Progress Alliance">
    <meta name="twitter:description" content="Parti Politique - Droite Républicaine - Présidé par A. Moore">
    <meta name="twitter:image" content="https://opengraph.b-cdn.net/production/images/43d6c5ae-10a4-4dc4-b536-310a5ce42cbf.png?token=I6U77DgD7UxGQeHPSNix-952LqUJuWeW2jAx3uJIMOc&height=675&width=1200&expires=33271327511">


</head>
<body>
    
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

    <section class="result-section">
        <h1>Résultat des élections</h1>

        <?php
        // Vérifier si des résultats sont disponibles
        if (!empty($candidates)) {
            // Variable pour savoir si on est au premier candidat
            $isWinner = true;

            // Parcourir les résultats des candidats
            foreach ($candidates as $candidate) {
                // Calculer le pourcentage du score
                $percentage = round($candidate['score'], 2);
                $scoreWidth = $percentage;  // Largeur en pourcentage pour la barre de score
        ?>

                <div class="result-container">
                    <div class="<?php echo $isWinner ? 'result-winner' : ''; ?>">
                        <div class="result-candidat">
                            <div class="candidat-img">
                                <img src="<?php echo htmlspecialchars($candidate['img_url']); ?>" alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                            </div>

                            <div class="candidat-stats">
                                <p><?php echo htmlspecialchars($candidate['name']); ?></p>
                                <div class="candidat-max">
                                    <div class="candidat-score" style="width: <?php echo $scoreWidth; ?>%;">
                                        <span><?php echo $percentage; ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>                    
                </div>

        <?php
                // Après le premier candidat, ne plus appliquer la classe 'winner'
                $isWinner = false;
            }
        } else {
            echo "<p>Aucun résultat disponible.</p>";
        }
        ?>
    </section>

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
<script src="js/gsap_anim.js"></script>
</html>
