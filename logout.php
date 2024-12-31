<?php
require 'config.php'; // Inclure le fichier de configuration
session_start(); // Start the session
session_destroy();
header("Location: index.php");
?>