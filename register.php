<?php
require_once 'config.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation basique
    if (strtolower($username) === 'admin') {
        $error = "Ce nom d'utilisateur est réservé.";
    } elseif (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide';
    } elseif ($password !== $password_confirm) {
        $error = 'Les mots de passe ne correspondent pas';
    } else {
        $db = Database::getInstance()->getConnection();

        // Vérifier doublon email/username
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = 'Un compte existe déjà avec cet email ou ce nom d\'utilisateur';
        } else {
            // Insérer l'utilisateur
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, avatar, created_at) VALUES (?, ?, ?, ?, NULL, NOW())");
            $stmt->execute([$username, $email, $hash, $full_name]);

            $user_id = $db->lastInsertId();

            // Créer la session et rediriger
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $full_name;

            // Mettre à jour le statut en ligne
            $stmt = $db->prepare("UPDATE users SET is_online = TRUE, last_seen = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);

            redirect('/index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - WorkSphere</title>
    <style>
        :root { --primary: #2563eb; --bg: #f3f4f6; --dark: #0f172a; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: white; padding: 28px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); width: 100%; max-width: 520px; }
        h1 { text-align: center; margin-bottom: 8px; color: var(--dark); }
        .subtitle { text-align: center; color: #6b7280; margin-bottom: 20px; }
        .form-group { margin-bottom: 14px; }
        label { display:block;margin-bottom:6px;font-weight:600;color:var(--dark); }
        input { width:100%; padding:10px;border-radius:8px;border:1px solid #e5e7eb;font-size:14px }
        .btn { width:100%; padding:12px;background:var(--primary);color:#fff;border:0;border-radius:8px;font-weight:600;cursor:pointer }
        .error { background:#fee;color:#a00;padding:12px;border-radius:8px;margin-bottom:12px;text-align:center }
        .muted { color:#6b7280;font-size:13px;margin-top:12px;text-align:center }
        .link { text-align:center;margin-top:12px }
        .link a { color: #2563eb; text-decoration:none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Créer un compte</h1>
        <p class="subtitle">Rejoignez WorkSphere et collaborez</p>

        <?php if ($error): ?>
            <div class="error"><?= escape($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required value="<?= escape($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="full_name">Nom complet</label>
                <input type="text" id="full_name" name="full_name" required value="<?= escape($_POST['full_name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= escape($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>

            <button type="submit" class="btn">Créer mon compte</button>
        </form>

        <div class="link">
            <p class="muted">Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
        </div>
    </div>
</body>
</html>