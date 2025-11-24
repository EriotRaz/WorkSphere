<?php
require_once 'config.php';

// Si déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Pour la démo, accepter "password" comme mot de passe
        if ($user && (password_verify($password, $user['password']) || $password === 'password')) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Mettre à jour le statut en ligne
            $stmt = $db->prepare("UPDATE users SET is_online = TRUE, last_seen = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            redirect('/index.php');
        } else {
            $error = 'Email ou mot de passe incorrect';
        }
    } else {
        $error = 'Veuillez remplir tous les champs';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Intranet Entreprise</title>
    <style>
        :root {
            --primary: #2563eb;
            --bg: #f3f4f6;
            --dark: #0f172a;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
            color: var(--dark);
        }
        .subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .error {
            background: #fee;
            color: #c00;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .demo-info {
            margin-top: 20px;
            padding: 16px;
            background: #f0f9ff;
            border-radius: 8px;
            font-size: 13px;
            color: #0369a1;
        }
        .demo-info strong {
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🌐 WorkSphere</h1>
        <p class="subtitle">Plateforme collaborative de communication</p>
        
        <?php if ($error): ?>
            <div class="error"><?= escape($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Se connecter</button>
        </form>

        <p style="margin-top:16px;display:flex;gap:8px;justify-content:center;font-size:13px">
            <a href="forgot_password.php" style="color:#2563eb;text-decoration:none">Mot de passe oublié ?</a>
            <span style="color:#ccc">|</span>
            <a href="register.php" style="color:#2563eb;text-decoration:none">Créer un compte</a>
        </p>
        
        <div class="demo-info">
            <strong>Comptes de démonstration :</strong>
            Email: <code>sary@entreprise.mg</code><br>
            Email: <code>marie@entreprise.mg</code><br>
            Email: <code>jean@entreprise.mg</code><br>
            <strong>Mot de passe:</strong> <code>password</code>
        </div>
    </div>
</body>
</html>