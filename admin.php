<?php
require_once 'config.php';

if (!isLoggedIn() || $_SESSION['username'] !== 'admin') {
    redirect('/index.php');
}

$db = Database::getInstance()->getConnection();

// Traitement CRUD
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajouter un utilisateur
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        if (strtolower($username) === 'admin') {
            $error = "Ce nom d'utilisateur est réservé.";
        } elseif (!$username || !$full_name || !$email || !$password) {
            $error = "Tous les champs sont obligatoires.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email invalide.";
        } else {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                $error = "Email ou nom d'utilisateur déjà utilisé.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, avatar, is_online, created_at) VALUES (?, ?, ?, ?, NULL, FALSE, NOW())");
                $stmt->execute([$username, $email, $hash, $full_name]);
                $success = "Utilisateur ajouté.";
            }
        }
    }
    // Modifier un utilisateur
    if (isset($_POST['edit_user'])) {
        $id = intval($_POST['id']);
        $username = trim($_POST['username']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        if ($id === 1 || strtolower($username) === 'admin') {
            $error = "Impossible de modifier l'admin principal.";
        } elseif (!$username || !$full_name || !$email) {
            $error = "Tous les champs sont obligatoires.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email invalide.";
        } else {
            $stmt = $db->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
            $stmt->execute([$email, $username, $id]);
            if ($stmt->fetch()) {
                $error = "Email ou nom d'utilisateur déjà utilisé.";
            } else {
                if ($password) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET username=?, email=?, full_name=?, password=? WHERE id=?");
                    $stmt->execute([$username, $email, $full_name, $hash, $id]);
                } else {
                    $stmt = $db->prepare("UPDATE users SET username=?, email=?, full_name=? WHERE id=?");
                    $stmt->execute([$username, $email, $full_name, $id]);
                }
                $success = "Utilisateur modifié.";
            }
        }
    }
    // Supprimer un utilisateur
    if (isset($_POST['delete_user'])) {
        $id = intval($_POST['id']);
        if ($id === 1) {
            $error = "Impossible de supprimer l'admin principal.";
        } else {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Utilisateur supprimé.";
        }
    }
}

// Récupérer la liste des utilisateurs
$users = $db->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Admin - WorkSphere</title>
    <style>
        body { background: #f3f4f6; font-family: Inter, Arial, sans-serif; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 6px 18px rgba(16,24,40,0.06); padding: 32px; }
        h1 { color: #2563eb; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        th, td { padding: 10px; border-bottom: 1px solid #eef2f7; text-align: left; }
        th { background: #f9fafb; }
        .btn { background: #2563eb; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; }
        .logout { float: right; color: #ef4444; text-decoration: none; margin-top: -32px; }
        .form-inline input { margin-right: 8px; }
        .error { background: #fee; color: #a00; padding: 12px; border-radius: 8px; margin-bottom: 16px; text-align: center; }
        .success { background: #e6ffed; color: #059669; padding: 12px; border-radius: 8px; margin-bottom: 16px; text-align: center; }
        .edit-form, .add-form { margin-bottom: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout">Déconnexion</a>
        <h1>👑 Administration WorkSphere</h1>
        <?php if ($error): ?><div class="error"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?= escape($success) ?></div><?php endif; ?>
        <h2>Ajouter un utilisateur</h2>
        <form method="POST" class="add-form">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="text" name="full_name" placeholder="Nom complet" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" name="add_user" class="btn">Ajouter</button>
        </form>
        <h2>Utilisateurs</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nom d'utilisateur</th>
                <th>Nom complet</th>
                <th>Email</th>
                <th>En ligne</th>
                <th>Créé le</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= escape($u['username']) ?></td>
                <td><?= escape($u['full_name']) ?></td>
                <td><?= escape($u['email']) ?></td>
                <td><?= $u['is_online'] ? '🟢' : '⚪' ?></td>
                <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                <td>
                    <?php if ($u['username'] !== 'admin'): ?>
                    <form method="POST" style="display:inline-block" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" name="delete_user" class="btn" style="background:#ef4444">Supprimer</button>
                    </form>
                    <button type="button" class="btn" onclick="document.getElementById('edit-<?= $u['id'] ?>').style.display='block'">Modifier</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($u['username'] !== 'admin'): ?>
            <tr id="edit-<?= $u['id'] ?>" class="edit-form" style="display:none;background:#f9fafb">
                <td colspan="7">
                    <form method="POST" style="display:flex;gap:8px;align-items:center">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <input type="text" name="username" value="<?= escape($u['username']) ?>" required>
                        <input type="text" name="full_name" value="<?= escape($u['full_name']) ?>" required>
                        <input type="email" name="email" value="<?= escape($u['email']) ?>" required>
                        <input type="password" name="password" placeholder="Nouveau mot de passe (laisser vide pour ne pas changer)">
                        <button type="submit" name="edit_user" class="btn">Enregistrer</button>
                        <button type="button" class="btn" style="background:#aaa" onclick="document.getElementById('edit-<?= $u['id'] ?>').style.display='none'">Annuler</button>
                    </form>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </table>
        <p><a href="index.php" class="btn">Retour à l'accueil</a></p>
    </div>
</body>
</html>
