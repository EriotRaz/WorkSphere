<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (strtolower($username) === 'admin') {
        $error = "Ce nom d'utilisateur est réservé.";
    } elseif (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide';
    } elseif ($password !== $password_confirm) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit faire au moins 6 caractères';
    } else {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = 'Un compte existe déjà avec cet email ou ce nom d\'utilisateur';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, avatar, created_at) VALUES (?, ?, ?, ?, NULL, NOW())");
            $stmt->execute([$username, $email, $hash, $full_name]);

            $user_id = $db->lastInsertId();

            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $full_name;

            $stmt = $db->prepare("UPDATE users SET is_online = TRUE, last_seen = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);

            redirect('/dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - Intranet</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-md);
        }

        .register-container {
            width: 100%;
            max-width: 450px;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .register-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: var(--white);
            padding: 40px var(--spacing-lg) var(--spacing-lg);
            text-align: center;
        }

        .register-header h1 {
            font-size: 28px;
            margin-bottom: var(--spacing-xs);
            color: var(--white);
        }

        .register-header p {
            font-size: 13px;
            opacity: 0.9;
            margin: 0;
        }

        .register-content {
            padding: var(--spacing-xl);
        }

        .register-content form {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .form-group {
            margin: 0;
        }

        .form-group label {
            font-size: 13px;
            margin-bottom: var(--spacing-xs);
            display: block;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-group input {
            width: 100%;
            padding: 11px;
            border: 1px solid var(--light-gray);
            border-radius: var(--radius-md);
            font-size: 14px;
            transition: all var(--transition-fast);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all var(--transition-base);
            margin-top: var(--spacing-sm);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }

        .register-footer {
            margin-top: var(--spacing-lg);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--light-gray);
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }

        .register-footer a {
            color: var(--primary);
            text-decoration: none;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            margin-bottom: var(--spacing-md);
        }

        .password-help {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <h1>📝 Créer un compte</h1>
                <p>Rejoignez l'intranet</p>
            </div>

            <div class="register-content">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Erreur :</strong> <?= escape($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="full_name">Nom complet</label>
                        <input type="text" id="full_name" name="full_name" required placeholder="Jean Dupont" value="<?= escape($_POST['full_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" required placeholder="jean.dupont" value="<?= escape($_POST['username'] ?? '') ?>">
                        <div class="password-help">✓ Sans espaces, lettres et chiffres</div>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" required placeholder="jean@exemple.fr" value="<?= escape($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••">
                        <div class="password-help">✓ Au moins 6 caractères</div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirmer le mot de passe</label>
                        <input type="password" id="password_confirm" name="password_confirm" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn-register">Créer mon compte</button>
                </form>

                <div class="register-footer">
                    Vous avez déjà un compte ?
                    <a href="login.php">Se connecter</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
