<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (!$email) {
        $error = 'Veuillez entrer votre email';
    } else {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Générer un token reset
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
            $stmt->execute([$token, $expiry, $user['id']]);
            
            $reset_link = BASE_URL . '/reset_password.php?token=' . $token;
            $success = "Si cet email existe, un lien de réinitialisation a été envoyé. (Lien: $reset_link)";
        } else {
            $success = "Si cet email existe, un lien de réinitialisation a été envoyé.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - WorkSphere</title>
    <style>
        :root { --primary: #2563eb; --dark: #0f172a; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Inter, system-ui, sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); width: 100%; max-width: 420px; }
        h1 { text-align: center; margin-bottom: 8px; color: var(--dark); }
        .subtitle { text-align: center; color: #6b7280; margin-bottom: 24px; font-size: 14px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: var(--dark); }
        input { width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; }
        .btn { width: 100%; padding: 12px; background: var(--primary); color: #fff; border: 0; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .success { background: #e6ffed; color: #059669; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .error { background: #fee; color: #a00; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .link { text-align: center; margin-top: 16px; font-size: 13px; }
        .link a { color: var(--primary); text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔑 Mot de passe oublié</h1>
        <p class="subtitle">Entrez votre email pour réinitialiser votre mot de passe</p>
        
        <?php if ($success): ?>
            <div class="success"><?= escape($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= escape($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn">Envoyer le lien</button>
        </form>
        
        <div class="link">
            <a href="login.php">← Retour à la connexion</a>
        </div>
    </div>
</body>
</html>
