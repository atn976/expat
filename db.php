<?php
$servername = "localhost"; // Utilisez "127.0.0.1" ou "::1" si "localhost" ne fonctionne pas
$username = "root"; // Votre nom d'utilisateur MySQL
$password = ""; // Votre mot de passe MySQL, laissez vide si vous n'avez pas de mot de passe
$dbname = "expat"; // Le nom de votre base de données

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
