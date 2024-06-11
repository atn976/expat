<?php
class Database {
    private static $instance = null;
    private $conn;

    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "expat";

    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Méthode statique pour obtenir l'instance unique de la connexion à la base de données
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Méthode pour récupérer la connexion à la base de données
    public function getConnection() {
        return $this->conn;
    }

    // Empêcher le clonage de l'objet
    private function __clone() {}

    // Empêcher la désérialisation de l'objet
    private function __wakeup() {}
}


$db = Database::getInstance();
$conn = $db->getConnection();


?>
