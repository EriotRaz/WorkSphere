<?php
require_once 'config.php';

if (isLoggedIn()) {
    // Mettre à jour le statut en ligne
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE users SET is_online = FALSE, last_seen = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Détruire la session
    session_unset();
    session_destroy();
}

redirect('/login.php');
?>