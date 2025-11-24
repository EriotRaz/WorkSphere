<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$token_valid = false;

if (empty($token)) {
    $error = 'Token de réinitialisation invalide';
} else {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, reset_expiry FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'Token invalide';
    } elseif (strtotime($user['reset_expiry']) < time()) {
        $error = 'Ce lien a expiré. Demandez un nouveau lien.';
    } else {
        $token_valid = true;
    }
}

if ($token_valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password)) {
        $error = 'Veuillez entrer un mot de passe';
    } elseif ($password !== $password_confirm) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit faire au moins 6 caractères';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?");
        $stmt->execute([$hash, $token]);

        $success = 'Mot de passe réinitialisé avec succès !';
        $token_valid = false;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - Intranet</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-md);
        }

        .reset-container {
            width: 100%;
            max-width: 420px;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .reset-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }

        .reset-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: var(--white);
            padding: 40px var(--spacing-lg) var(--spacing-lg);
            text-align: center;
        }

        .reset-header h1 {
            font-size: 28px;
            margin-bottom: var(--spacing-xs);
            color: var(--white);
        }

        .reset-header p {
            font-size: 13px;
            opacity: 0.9;
            margin: 0;
        }

        .reset-content {
            padding: var(--spacing-xl);
        }

        .form-group {
            margin-bottom: var(--spacing-md);
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
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
        }

        .btn-confirm {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all var(--transition-base);
            margin-top: var(--spacing-sm);
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
        }

        .reset-footer {
            margin-top: var(--spacing-lg);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--light-gray);
            text-align: center;
            font-size: 13px;
        }

        .reset-footer a {
            color: var(--primary);
            text-decoration: none;
        }

        .reset-footer a:hover {
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
    <div class="reset-container">
        <div class="reset-box">
            <div class="reset-header">
                <h1>🔑 Nouveau mot de passe</h1>
                <p>Créez un mot de passe sécurisé</p>
            </div>

            <div class="reset-content">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Erreur :</strong> <?= escape($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        ✓ <?= escape($success) ?> 
                        <br><a href="login.php" style="color: inherit; text-decoration: underline;">Se connecter →</a>
                    </div>
                <?php endif; ?>

                <?php if ($token_valid): ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="password">Nouveau mot de passe</label>
                            <input type="password" id="password" name="password" required placeholder="••••••••">
                            <div class="password-help">✓ Au moins 6 caractères</div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">Confirmer le mot de passe</label>
                            <input type="password" id="password_confirm" name="password_confirm" required placeholder="••••••••">
                        </div>

                        <button type="submit" class="btn-confirm">Réinitialiser le mot de passe</button>
                    </form>
                <?php endif; ?>

                <div class="reset-footer">
                    <a href="login.php">← Retour à la connexion</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
