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
    <title>Mon profil - Intranet</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: var(--bg);
            padding: var(--spacing-md);
        }

        .profile-wrapper {
            max-width: 700px;
            margin: 0 auto;
        }

        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            padding: 40px var(--spacing-lg);
            text-align: center;
            color: var(--white);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            margin: 0 auto var(--spacing-lg);
            border: 3px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .profile-header h1 {
            font-size: 28px;
            margin-bottom: var(--spacing-xs);
            color: var(--white);
        }

        .profile-role {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .profile-body {
            background: var(--white);
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .profile-content {
            padding: var(--spacing-xl);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: var(--spacing-lg);
            font-weight: 600;
            transition: all var(--transition-fast);
        }

        .back-link:hover {
            gap: 8px;
        }

        .back-link::before {
            content: '← ';
            margin-right: 4px;
        }

        /* Statistiques */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-xl);
            border-bottom: 1px solid var(--light-gray);
        }

        .stat-card {
            text-align: center;
            padding: var(--spacing-md);
            background: var(--bg);
            border-radius: var(--radius-md);
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Sections */
        .profile-section {
            margin-bottom: var(--spacing-xl);
        }

        .profile-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: var(--spacing-md);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--primary);
            color: var(--text-dark);
        }

        .form-group {
            margin-bottom: var(--spacing-md);
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: var(--spacing-xs);
            color: var(--text-dark);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid var(--light-gray);
            border-radius: var(--radius-md);
            font-size: 14px;
            font-family: inherit;
            transition: all var(--transition-fast);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            padding: 11px 20px;
            border: none;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-base);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        /* Alertes */
        .alert {
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-lg);
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .alert-danger {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #7f1d1d;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .profile-header {
                padding: 30px var(--spacing-md);
            }

            .profile-header h1 {
                font-size: 22px;
            }

            .profile-content {
                padding: var(--spacing-lg);
            }

            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="profile-wrapper">
        <div class="profile-header">
            <div class="profile-avatar">👤</div>
            <h1><?= escape($user['full_name']) ?></h1>
            <div class="profile-role"><?= $user['role'] ?></div>
        </div>

        <div class="profile-body">
            <div class="profile-content">
                <a href="index.php" class="back-link">Retour à l'accueil</a>

                <?php if ($success): ?>
                    <div class="alert alert-success">✓ <?= escape($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">✗ <?= escape($error) ?></div>
                <?php endif; ?>

                <!-- Statistiques -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= $message_count ?></div>
                        <div class="stat-label">Messages</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $team_count ?></div>
                        <div class="stat-label">Équipes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= date('d/m/y', strtotime($user['created_at'])) ?></div>
                        <div class="stat-label">Inscrit</div>
                    </div>
                </div>

                <!-- Modifier le profil -->
                <div class="profile-section">
                    <h2 class="section-title">📝 Mon profil</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-group">
                            <label for="full_name">Nom complet</label>
                            <input type="text" id="full_name" name="full_name" value="<?= escape($user['full_name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="bio">Biographie</label>
                            <textarea id="bio" name="bio" placeholder="Parlez-nous de vous...<?= escape($user['bio'] ?? '') ?>"><?= escape($user['bio'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
                    </form>
                </div>

                <!-- Changer le mot de passe -->
                <div class="profile-section">
                    <h2 class="section-title">🔐 Sécurité</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label for="old_password">Ancien mot de passe</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Nouveau mot de passe</label>
                            <input type="password" id="password" name="password" required>
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">Confirmer le mot de passe</label>
                            <input type="password" id="password_confirm" name="password_confirm" required>
                        </div>

                        <button type="submit" class="btn btn-primary">🔑 Changer le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
