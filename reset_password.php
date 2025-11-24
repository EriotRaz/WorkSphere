<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    $error = 'Token invalide';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if ($password !== $password_confirm) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit faire au moins 6 caractères';
    } else {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $error = 'Token expiré ou invalide';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?");
            $stmt->execute([$hash, $token]);
            $success = 'Mot de passe réinitialisé avec succès!';
            $token = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - WorkSphere</title>
    <style>
        :root { --primary: #2563eb; --dark: #0f172a; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Inter, system-ui, sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); width: 100%; max-width: 420px; }
        h1 { text-align: center; margin-bottom: 8px; color: var(--dark); }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: var(--dark); }
        input { width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; }
        .btn { width: 100%; padding: 12px; background: var(--primary); color: #fff; border: 0; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .success { background: #e6ffed; color: #059669; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .error { background: #fee; color: #a00; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .link { text-align: center; margin-top: 16px; }
        .link a { color: var(--primary); text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔐 Réinitialiser le mot de passe</h1>
        
        <?php if ($success): ?>
            <div class="success"><?= escape($success) ?></div>
            <div class="link"><a href="login.php">Se connecter →</a></div>
        <?php elseif ($error): ?>
            <div class="error"><?= escape($error) ?></div>
            <div class="link"><a href="forgot_password.php">Renvoyer le lien</a></div>
        <?php elseif ($token): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                <button type="submit" class="btn">Réinitialiser</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
