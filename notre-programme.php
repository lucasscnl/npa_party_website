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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
    <title>National Progress Alliance</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <meta property="og:title" content="National Progress Alliance" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://npa.verenium.be/" />
    <meta property="og:image" content="https://npa.verenium.be/img/1129x500_npa" />
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

    <section class="programme-container">
        <h1>Notre programme en 6 points</h1>

        <div class="programme-category">
            <h1>Vision du parti</h1>
            <div class="programme-desc">
                <p class="programme-resum">sécurité, croissance, économie et souveraineté nationale</p>
                <div class="programme-arg">
                    <p>Notre parti a pour but de régler les soucis qui touchent l’état directement avant de s’impliquer dans les affaires qui lui sont extérieures.</p>
                </div>
            </div>
        </div>

        <div class="programme-category">
            <h1>économie</h1>
            <div class="programme-desc">
                <p class="programme-resum">réforme et soutient</p>
                <div class="programme-arg">
                    <span class="arg-nbr">1</span>
                    <p>Nous voulons réformer le système d’impositions, pour produire un système fiscal qui soit adapté à tous touts en favorisant une croissance économique.</p>
                </div>
                <div class="programme-arg">
                    <span class="arg-nbr">2</span>
                    <p>Nous souhaitons également marquer un soutien fort aux entreprises qui sont la base de notre économie et qui, souvent, sont délaissées par les gouvernements.</p>
                </div>
            </div>
        </div>

        <div class="programme-category">
            <h1>santé</h1>
            <div class="programme-desc">
                <p class="programme-resum">équitable, défense, augmentation</p>
                <div class="programme-arg">
                    <span class="arg-nbr">1</span>
                    <p>Nous souhaitons que notre système de santé soit plus accessible, aujourd’hui des soins qui en Europe sont remboursés, coûtent 4 fois plus cher aux familles. Nous proposons le développement d'assurances privées qui développera en plus notre économie.</p>
                </div>
                <div class="programme-arg">
                    <span class="arg-nbr">2</span>
                    <p>Nous sommes convaincus que notre pays peut devenir plus sécuritaire également par le développement médical. Nous voulons augmenter ce budget qui nous permettra de mettre en sécurité bon nombre de citoyens mais aussi de promouvoir notre état.</p>
                </div>
            </div>
        </div>

        <div class="programme-category">
            <h1>environement</h1>
            <div class="programme-desc">
                <p class="programme-resum">durable, mixte, modernisation</p>
                <div class="programme-arg">
                    <span class="arg-nbr">1</span>
                    <p>Nous comptons continuer le développement des énergies durables, qui sont sans doute l’avenir de notre énergie.</p>
                </div>
                <div class="programme-arg">
                    <span class="arg-nbr">2</span>
                    <p>Toutefois, aujourd’hui, cette énergie n’est pas prête à prendre la charge d’un état entier. C’est pourquoi nous prononçons une continuité mixte entre la modernisation du nucléaire et le développement des énergies durables.</p>
                </div>
                <div class="programme-arg">
                    <span class="arg-nbr">3</span>
                    <p>Nous souhaitons moderniser nos infrastructures. Cela viendra compléter notre acte environnemental tout en optimisant nos ressources.</p>
                </div>
            </div>
        </div>
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
    </script>
</body>
<script src="js/gsap_anim.js"></script>
</html>