<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Veuillez entrer une adresse email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide';
    } else {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
            $stmt->execute([$token, $expiry, $user['id']]);

            $reset_link = BASE_URL . "/reset_password.php?token=" . $token;

            $email_body = "Bonjour,\n\nPour réinitialiser votre mot de passe, cliquez sur le lien ci-dessous :\n" . $reset_link . "\n\nCe lien expire dans 1 heure.\n\nSi vous n'avez pas demandé cette réinitialisation, ignorez ce message.";

            mail($email, "Réinitialisation de mot de passe - Intranet", $email_body, "From: noreply@intranet.local");

            $success = 'Un email de réinitialisation a été envoyé à ' . escape($email);
        } else {
            $success = 'Un email de réinitialisation a été envoyé (si le compte existe)';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Intranet</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-md);
        }

        .forgot-container {
            width: 100%;
            max-width: 400px;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .forgot-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }

        .forgot-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: var(--white);
            padding: 40px var(--spacing-lg) var(--spacing-lg);
            text-align: center;
        }

        .forgot-header h1 {
            font-size: 28px;
            margin-bottom: var(--spacing-xs);
            color: var(--white);
        }

        .forgot-header p {
            font-size: 13px;
            opacity: 0.9;
            margin: 0;
        }

        .forgot-content {
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
            border-color: #f5576c;
            box-shadow: 0 0 0 3px rgba(245, 87, 108, 0.1);
        }

        .btn-reset {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all var(--transition-base);
            margin-top: var(--spacing-sm);
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 87, 108, 0.3);
        }

        .forgot-footer {
            margin-top: var(--spacing-lg);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--light-gray);
            text-align: center;
            font-size: 13px;
        }

        .forgot-footer a {
            color: var(--primary);
            text-decoration: none;
        }

        .forgot-footer a:hover {
            text-decoration: underline;
        }

        .help-text {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: var(--spacing-sm);
            line-height: 1.5;
        }

        .alert {
            margin-bottom: var(--spacing-md);
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-box">
            <div class="forgot-header">
                <h1>🔐 Mot de passe oublié</h1>
                <p>Réinitialisez votre accès</p>
            </div>

            <div class="forgot-content">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Erreur :</strong> <?= escape($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        ✓ <?= escape($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" required placeholder="vous@exemple.fr" value="<?= escape($_POST['email'] ?? '') ?>">
                        <div class="help-text">
                            Entrez l'email associé à votre compte. Nous vous enverrons un lien pour réinitialiser votre mot de passe.
                        </div>
                    </div>

                    <button type="submit" class="btn-reset">Envoyer le lien</button>
                </form>

                <div class="forgot-footer">
                    Vous vous rappelez votre mot de passe ?
                    <br><br>
                    <a href="login.php">← Retour à la connexion</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
