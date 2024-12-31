<?php

// Define timezone
define('TIMEZONE', 'Europe/Paris');
date_default_timezone_set(TIMEZONE);

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'npa_root');
define('DB_PASS', 'ScANluc11!');
define('DB_NAME', 'npa_db');

// Establishing database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connexion réussie";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// debug mode
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Application constants
define('BASE_URL', 'npa.verenium.be');
define('APP_NAME', 'National Progress Alliance');

?>