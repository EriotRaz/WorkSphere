<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        if (!$full_name) {
            $error = 'Le nom complet est obligatoire';
        } else {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, bio = ? WHERE id = ?");
            $stmt->execute([$full_name, $bio, $user_id]);
            $_SESSION['full_name'] = $full_name;
            $success = 'Profil mis à jour';
            $user['full_name'] = $full_name;
            $user['bio'] = $bio;
        }
    }
    
    if ($action === 'change_password') {
        $old_password = $_POST['old_password'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        if (!password_verify($old_password, $user['password']) && $old_password !== 'password') {
            $error = 'Ancien mot de passe incorrect';
        } elseif ($password !== $password_confirm) {
            $error = 'Les mots de passe ne correspondent pas';
        } elseif (strlen($password) < 6) {
            $error = 'Le mot de passe doit faire au moins 6 caractères';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $user_id]);
            $success = 'Mot de passe changé avec succès';
        }
    }
}

$message_count = $db->query("SELECT COUNT(*) as cnt FROM messages WHERE user_id = $user_id")->fetch()['cnt'];
$team_count = $db->query("SELECT COUNT(*) as cnt FROM team_members WHERE user_id = $user_id")->fetch()['cnt'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - WorkSphere</title>
    <style>
        :root { --primary: #2563eb; --dark: #0f172a; --bg: #f3f4f6; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Inter, system-ui, sans-serif; }
        body { background: var(--bg); min-height: 100vh; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); overflow: hidden; }
        header { background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%); color: #fff; padding: 40px 20px; text-align: center; }
        .avatar { width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); margin: 0 auto 16px; }
        h1 { font-size: 24px; margin-bottom: 4px; }
        .role { font-size: 12px; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; }
        .content { padding: 32px; }
        .section { margin-bottom: 32px; }
        .section h2 { font-size: 16px; margin-bottom: 16px; color: var(--primary); border-bottom: 2px solid #eef2f7; padding-bottom: 8px; }
        .stat { display: inline-block; margin-right: 32px; }
        .stat-value { font-size: 24px; font-weight: 700; color: var(--primary); }
        .stat-label { font-size: 12px; color: #6b7280; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: var(--dark); }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit; }
        textarea { resize: vertical; min-height: 100px; }
        .btn { background: var(--primary); color: #fff; border: 0; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn.secondary { background: #6b7280; }
        .success { background: #e6ffed; color: #059669; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .error { background: #fee; color: #a00; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .back { display: inline-block; margin-bottom: 16px; color: var(--primary); text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <img class="avatar" src="<?= escape($user['avatar'] ?: AVATAR_DEFAULT) ?>" alt="Avatar">
            <h1><?= escape($user['full_name']) ?></h1>
            <div class="role"><?= $user['role'] ?></div>
        </header>
        
        <div class="content">
            <a href="index.php" class="back">← Retour à l'accueil</a>
            
            <?php if ($success): ?><div class="success"><?= escape($success) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="error"><?= escape($error) ?></div><?php endif; ?>
            
            <!-- Statistiques -->
            <div class="section">
                <div class="stat">
                    <div class="stat-value"><?= $message_count ?></div>
                    <div class="stat-label">Messages</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?= $team_count ?></div>
                    <div class="stat-label">Équipes</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
                    <div class="stat-label">Inscrit</div>
                </div>
            </div>
            
            <!-- Modifier le profil -->
            <div class="section">
                <h2>📝 Profil</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="full_name" value="<?= escape($user['full_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" placeholder="Parlez-nous de vous..."><?= escape($user['bio'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn">Enregistrer</button>
                </form>
            </div>
            
            <!-- Changer le mot de passe -->
            <div class="section">
                <h2>🔐 Sécurité</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label>Ancien mot de passe</label>
                        <input type="password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" name="password_confirm" required>
                    </div>
                    <button type="submit" class="btn">Changer le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
