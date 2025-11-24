<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_message':
                $content = trim($_POST['content'] ?? '');
                if (!empty($content)) {
                    $stmt = $db->prepare("INSERT INTO messages (user_id, content) VALUES (?, ?)");
                    $stmt->execute([$user_id, $content]);
                    $_SESSION['success'] = 'Message publié avec succès';
                }
                break;
            
            case 'edit_message':
                $message_id = intval($_POST['message_id']);
                $content = trim($_POST['content'] ?? '');
                
                $stmt = $db->prepare("SELECT user_id FROM messages WHERE id = ?");
                $stmt->execute([$message_id]);
                $msg = $stmt->fetch();
                
                if ($msg && ($msg['user_id'] === $user_id || $_SESSION['username'] === 'admin')) {
                    $stmt = $db->prepare("UPDATE messages SET content = ?, edited_at = NOW() WHERE id = ?");
                    $stmt->execute([$content, $message_id]);
                    $_SESSION['success'] = 'Message modifié';
                } else {
                    $_SESSION['error'] = 'Impossible de modifier ce message';
                }
                break;
            
            case 'delete_message':
                $message_id = intval($_POST['message_id']);
                
                $stmt = $db->prepare("SELECT user_id FROM messages WHERE id = ?");
                $stmt->execute([$message_id]);
                $msg = $stmt->fetch();
                
                if ($msg && ($msg['user_id'] === $user_id || $_SESSION['username'] === 'admin')) {
                    $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
                    $stmt->execute([$message_id]);
                    $_SESSION['success'] = 'Message supprimé';
                } else {
                    $_SESSION['error'] = 'Impossible de supprimer ce message';
                }
                break;
                
            case 'add_team':
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                if (!empty($name)) {
                    $stmt = $db->prepare("INSERT INTO teams (name, description, created_by) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $description, $user_id]);
                    $_SESSION['success'] = 'Équipe créée avec succès';
                }
                break;
            
            case 'delete_team':
                $team_id = intval($_POST['team_id']);
                
                $stmt = $db->prepare("SELECT created_by FROM teams WHERE id = ?");
                $stmt->execute([$team_id]);
                $team = $stmt->fetch();
                
                if ($team && ($team['created_by'] === $user_id || $_SESSION['username'] === 'admin')) {
                    $stmt = $db->prepare("DELETE FROM teams WHERE id = ?");
                    $stmt->execute([$team_id]);
                    $_SESSION['success'] = 'Équipe supprimée';
                }
                break;
                
            case 'add_event':
                $title = trim($_POST['title'] ?? '');
                $event_date = $_POST['event_date'] ?? '';
                $event_time = $_POST['event_time'] ?? '00:00:00';
                $location = trim($_POST['location'] ?? '');
                $description = trim($_POST['description'] ?? '');
                
                if (!empty($title) && !empty($event_date)) {
                    $stmt = $db->prepare("INSERT INTO events (title, description, event_date, event_time, location, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $event_date, $event_time, $location, $user_id]);
                    $_SESSION['success'] = 'Événement ajouté avec succès';
                }
                break;
            
            case 'rsvp_event':
                $event_id = intval($_POST['event_id']);
                $status = $_POST['status'] ?? 'pending';
                
                $stmt = $db->prepare("SELECT id FROM event_rsvp WHERE event_id = ? AND user_id = ?");
                $stmt->execute([$event_id, $user_id]);
                
                if ($stmt->fetch()) {
                    $stmt = $db->prepare("UPDATE event_rsvp SET status = ? WHERE event_id = ? AND user_id = ?");
                    $stmt->execute([$status, $event_id, $user_id]);
                } else {
                    $stmt = $db->prepare("INSERT INTO event_rsvp (event_id, user_id, status) VALUES (?, ?, ?)");
                    $stmt->execute([$event_id, $user_id, $status]);
                }
                $_SESSION['success'] = 'RSVP enregistré';
                break;
            
            case 'register':
                break;
                
            default:
                $_SESSION['error'] = 'Action non reconnue';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erreur : ' . $e->getMessage();
    }
}

redirect('/index.php');
?>
