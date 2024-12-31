<?php
require 'config.php'; // Inclure le fichier de configuration
session_start();

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
    // Requête pour obtenir les 3 cartes les plus récentes
    $stmt = $pdo->prepare("SELECT * FROM news ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Vérifiez si la méthode est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $title = trim($_POST['news-title'] ?? '');
    $description = trim($_POST['news-desc'] ?? '');
    $category = trim($_POST['news-categ'] ?? '');
    $author = $_SESSION['username'] ?? 'Anonyme'; // Par défaut si non connecté
    $croppedImageData = $_POST['cropped-image-data'] ?? null;

    // Validation basique
    if (empty($title) || empty($description) || empty($croppedImageData)) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } else {
        try {
            // Décoder l'image croppée en Base64
            list($type, $data) = explode(';', $croppedImageData);
            list(, $data) = explode(',', $data);
            $imageData = base64_decode($data);

            // Sauvegarder l'image temporairement
            $tempFilePath = 'temp_image.jpg';
            if (!file_put_contents($tempFilePath, $imageData)) {
                throw new Exception("Erreur lors de la sauvegarde temporaire de l'image.");
            }

            // Envoyer l'image à Imgur
            $imgurClientID = '22624c3a2e57524'; // Remplacez par votre Client ID Imgur
            $uploadUrl = 'https://api.imgur.com/3/image';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $uploadUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Client-ID $imgurClientID"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'image' => base64_encode(file_get_contents($tempFilePath))
            ]);

            $response = curl_exec($ch);
            $responseData = json_decode($response, true);
            curl_close($ch);

            // Supprimer le fichier temporaire
            unlink($tempFilePath);

            if (isset($responseData['success']) && $responseData['success'] === true) {
                $image_url = $responseData['data']['link'];

                $stmt = $pdo->prepare("INSERT INTO news (image_url, category, title, description, author) 
                                        VALUES (:image_url, :category, :title, :description, :author)");
                $stmt->bindParam(':image_url', $image_url, PDO::PARAM_STR);
                $stmt->bindParam(':category', $category, PDO::PARAM_STR);
                $stmt->bindParam(':title', $title, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':author', $author, PDO::PARAM_STR);
                $stmt->execute();

                $success = "La news a été ajoutée avec succès.";
            } else {
                throw new Exception("Erreur lors de l'upload de l'image sur Imgur.");
            }
        } catch (Exception $e) {
            $error = "Une erreur est survenue : " . $e->getMessage();
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Si succès ou erreur, garder le message visible
    if (isset($error)) {
        echo "<p class='error'>" . htmlspecialchars($error) . "</p>";
    } elseif (isset($success)) {
        echo "<p class='success'>" . htmlspecialchars($success) . "</p>";
    }
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">  
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>


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

<section class="container form-section">
        <button class="close-btn"><i class="fa-solid fa-plus"></i></button>
        <article class="form-container">
            <h1>+ News</h1>
            <?php if (isset($success)): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php elseif (isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="news-title">Titre</label><br>
                <input type="text" id="news-title" name="news-title" required><br>

                <label for="news-desc">Description</label><br>
                <input type="text" id="news-desc" name="news-desc" required><br>

                <label for="news-categ">Catégorie</label><br>
                <select name="news-categ" id="news-categ">
                    <option value="">Choisi une catégorie</option>
                    <option value="communiqué">Communiqué</option>
                    <option value="lois">Lois</option>
                    <option value="meeting">Meeting</option>
                    <option value="site web">Site Web</option>
                    <option value="Autre">Autres</option>
                </select><br>

                <label for="news-img">Image (Fichier)</label><br>
                <input type="file" id="news-img" name="news-img" accept="image/*" required><br>

                <div id="image-preview-container">
                    <img id="image-preview" style="max-width: 100%; display: none;">
                </div>

                <button type="button" id="crop-btn" style="display: none;">Rogner l'image</button>
                <input type="hidden" id="cropped-image-data" name="cropped-image-data">

                <input class="submit-btn" type="submit" value="Publier">
            </form>
        </article>
    </section>
    <script src="js/img.js"></script>
    
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

    <section class="container">
        <div class="slider">
            <div class="slider-img">
                <img src="img/1129x500_npa.jpg" alt="">
            </div>
            <div class="slider-txt">
                <span>l'amérique debout,</span><br>
                <span style="color: var(--r)">l'avenir avec nous!</span>
            </div>
        </div>
    </section>

    <section class="container card-section" style="background-color: var(--b); height: 300px; margin-bottom: 200px;">
        <div class="card-container">
            <?php foreach ($cards as $card): ?>
                <div class="card">
                    <div class="card-img">
                        <img src="<?= htmlspecialchars($card['image_url']) ?>" alt="">
                        <span class="card-category <?php echo $card['category'] == 'communiqué' ? 'release' : ($card['category'] == 'lois' ? 'laws' : 'event'); ?>">
                            <?= htmlspecialchars($card['category']) ?>
                        </span>
                    </div>
                    <div class="card-content">
                        <span class="card-title"><?= htmlspecialchars($card['title']) ?></span>
                        <p class="card-desc"><?= htmlspecialchars($card['description']) ?></p>
                        <span class="card-author">
                            <?= date('d/m', strtotime($card['created_at'])) ?> - By <?= htmlspecialchars($card['author']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php
                if ($connect == true && $_SESSION['edit'] === 1) {
                    echo '<div class="card add"><button class="add-btn"><i class="fa-solid fa-plus"></i></button></div>';
                }
            ?>
            
        </div>
    </section>

    <section class="container president-section" style="background-color: var(--r); height: 500px; margin-bottom: 100px;">
        <div class="president-container">
            <div class="president-img">
                <img src="img/650x500_npa.jpg" alt="650x500">
                <div class="president-name">
                    <span>Alexander</span><br>
                    <span style="color: var(--r);">Moore</span>
                </div>
            </div>
            <div class="president-desc">
                <span class="president-title">notre président</span>
                <p>Alexander Moore, né en Californie le 11 mars 1998. Aujourd'hui, il a 26 ans et est l'un des plus jeunes politiciens américains à avoir déjà été propulsé au poste de gouverneur d'un état. Il a rejoint les Républicains en à 21 ans mais a décidé de créer son propre parti pour défendre l'entièreté de ses idées. Très bon orateur, il s'impose comme le renouveau en politique et son avenir s'annonce prometteur.</p>
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
</body>
<script src="js/gsap_anim.js"></script>
<script src="js/form.js"></script>
</html>
