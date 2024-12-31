<?php
session_start(); // Start the session

// Include database configuration
require_once 'config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['user']);
    $password = trim($_POST['pass']);

    if (!empty($username) && !empty($password)) {
        try {
            // Prepare the SQL statement to find the user
            $stmt = $pdo->prepare("SELECT username, password, edit, perm FROM users WHERE username = :username AND password = :password");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            // Fetch the user
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Credentials are correct, create session
                $_SESSION['username'] = $user['username'];
                $_SESSION['edit'] = $user['edit'];
                $_SESSION['perm'] = $user['perm'];

                // Redirect to dashboard or another page
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in both fields.";
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
    
    <!-- <header>
        <nav>
            <a href="notre-programme">notre <strong>programme</strong></a>
            <a href="nos-evenements">nos <strong>évènements</strong></a>
            <a href="index" class="logo"><img src="img/logo.png" alt=""></a>
            <a href="elections">les <strong>élèctions</strong></a>
            <a href="login">Se <strong>Connecter</strong></a>
        </nav>
    </header> -->

    <section class="container login-section">
        <a href="index"><img src="img/logo.png" alt=""></a>
        <article class="login-container">
            <h1>Login</h1>
            <form action="" method="POST">
                <?php if (!empty($error)): ?>
                    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <label for="user">Username</label><br>
                <input type="text" id="user" name="user" required><br>

                <label for="pass">Password</label><br>
                <input type="password" id="pass" name="pass" required><br>

                <input class="connect-btn" type="submit" value="Se connecter">
            </form>
        </article>
    </section>

    <!-- <footer>
        <nav>
            <a href="">le <strong>parti</strong></a>
            <a href="">notre <strong>programme</strong></a>
            <a href=""><img src="logo.png" alt=""></a>
            <a href="">nos <strong>évènements</strong></a>
            <a href="">les <strong>élèctions</strong></a>
        </nav>
        <span>&copy Lucass - 2024</span>
    </footer> -->
</body>
</html>
