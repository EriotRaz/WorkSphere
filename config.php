<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'intranet_entreprise');
define('DB_USER', 'root');          // Votre utilisateur MySQL
define('DB_PASS', '');              // Votre mot de passe MySQL
define('BASE_URL', 'http://localhost/intranet');  // URL de base

// Configuration de session
define('SESSION_NAME', 'INTRANET_SESSION');
define('SESSION_LIFETIME', 86400); // 24 heures

// Configuration de l'application

define('AVATAR_DEFAULT', 'https://i.pravatar.cc/48?img=1');

// Fuseau horaire
date_default_timezone_set('Indian/Antananarivo');

// Démarrage de la session
session_name(SESSION_NAME);
session_start();

// Classe de connexion à la base de données
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fonction pour rediriger
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Fonction pour échapper les données HTML
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Fonction pour formater les dates
function timeAgo($timestamp) {
    $diff = time() - strtotime($timestamp);
    
    if ($diff < 60) return $diff . 's';
    if ($diff < 3600) return floor($diff / 60) . 'm';
    if ($diff < 86400) return floor($diff / 3600) . 'h';
    if ($diff < 604800) return floor($diff / 86400) . 'j';
    
    return date('d/m/Y', strtotime($timestamp));
}

function daysUntil($date) {
    $target = strtotime($date);
    $today = strtotime(date('Y-m-d'));
    $diff = ceil(($target - $today) / 86400);
    
    if ($diff > 1) return $diff . ' jours';
    if ($diff === 1) return 'Demain';
    if ($diff === 0) return "Aujourd'hui";
    return abs($diff) . ' jours passés';
}
?>