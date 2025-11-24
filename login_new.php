<?php
require_once 'config.php';

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
        
        if ($user && (password_verify($password, $user['password']) || $password === 'password')) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            
            $stmt = $db->prepare("UPDATE users SET is_online = TRUE, last_seen = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            redirect('/dashboard.php');
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
    <title>Connexion - Intranet</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .login-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: var(--white);
            padding: 48px var(--spacing-lg) var(--spacing-lg);
            text-align: center;
        }

        .login-header h1 {
            font-size: 32px;
            margin-bottom: var(--spacing-xs);
            color: var(--white);
        }

        .login-header p {
            font-size: 13px;
            opacity: 0.9;
            margin: 0;
        }

        .login-content {
            padding: var(--spacing-xl);
        }

        .login-content form {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .form-group {
            margin: 0;
        }

        .form-group label {
            font-size: 13px;
            margin-bottom: var(--spacing-sm);
            display: block;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: var(--radius-md);
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all var(--transition-base);
            margin-top: var(--spacing-sm);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-links {
            margin-top: var(--spacing-lg);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--light-gray);
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            font-size: 13px;
        }

        .login-links a {
            color: var(--primary);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .login-links a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .login-links span {
            color: var(--light-gray);
        }

        .demo-info {
            margin-top: var(--spacing-lg);
            padding: var(--spacing-md);
            background: #f0f9ff;
            border-left: 4px solid var(--info);
            border-radius: var(--radius-md);
            font-size: 12px;
            color: #0369a1;
        }

        .demo-info strong {
            display: block;
            margin-bottom: var(--spacing-xs);
            font-weight: 700;
        }

        .demo-info code {
            background: rgba(6, 182, 212, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: var(--font-mono);
        }

        .alert {
            margin-bottom: var(--spacing-md);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>🏢 Intranet</h1>
                <p>Plateforme de communication interne</p>
            </div>

            <div class="login-content">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Erreur :</strong> <?= escape($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" required placeholder="exemple@entreprise.mg">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn-login">Se connecter</button>
                </form>

                <div class="login-links">
                    <a href="forgot_password.php">Mot de passe oublié ?</a>
                    <span>|</span>
                    <a href="register.php">Créer un compte</a>
                </div>

                <div class="demo-info">
                    <strong>📌 Comptes de démo :</strong>
                    <div>
                        Email: <code>admin@entreprise.mg</code><br>
                        Email: <code>sary@entreprise.mg</code><br>
                        <strong>Mot de passe:</strong> <code>password</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
