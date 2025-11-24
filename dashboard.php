<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Statistiques
$users_count = $db->query("SELECT COUNT(*) as cnt FROM users")->fetch()['cnt'];
$messages_count = $db->query("SELECT COUNT(*) as cnt FROM messages")->fetch()['cnt'];
$events_count = $db->query("SELECT COUNT(*) as cnt FROM events WHERE event_date >= CURDATE()")->fetch()['cnt'];
$teams_count = $db->query("SELECT COUNT(*) as cnt FROM teams")->fetch()['cnt'];

$current_user = $db->prepare("SELECT * FROM users WHERE id = ?")->execute([$user_id]) ? $db->query("SELECT * FROM users WHERE id = $user_id")->fetch() : [];

// Activité récente
$recent_activity = $db->query("
    SELECT 'message' as type, m.id, m.created_at, u.full_name, CONCAT('Message de ', u.full_name) as action
    FROM messages m
    JOIN users u ON m.user_id = u.id
    UNION ALL
    SELECT 'event', e.id, e.created_at, u.full_name, CONCAT('Événement: ', e.title)
    FROM events e
    JOIN users u ON e.created_by = u.id
    ORDER BY created_at DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - WorkSphere</title>
    <style>
        :root { --primary: #2563eb; --dark: #0f172a; --bg: #f3f4f6; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Inter, system-ui, sans-serif; }
        body { background: var(--bg); }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%); color: #fff; padding: 40px; border-radius: 12px; margin-bottom: 24px; }
        h1 { font-size: 28px; margin-bottom: 8px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .stat-value { font-size: 32px; font-weight: 700; color: var(--primary); }
        .stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }
        .section { background: #fff; padding: 24px; border-radius: 12px; margin-bottom: 24px; }
        .section h2 { color: var(--primary); margin-bottom: 16px; font-size: 18px; }
        .activity-item { padding: 12px 0; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
        .activity-item:last-child { border-bottom: none; }
        .links { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
        .link-btn { background: var(--primary); color: #fff; padding: 12px; border-radius: 8px; text-decoration: none; text-align: center; font-size: 13px; font-weight: 600; }
        .back { display: inline-block; margin-bottom: 16px; color: var(--primary); text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back">← Retour à l'accueil</a>
        
        <div class="header">
            <h1>📊 Tableau de bord</h1>
            <p>Bienvenue <?= escape($current_user['full_name']) ?> 👋</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $users_count ?></div>
                <div class="stat-label">Utilisateurs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $messages_count ?></div>
                <div class="stat-label">Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $events_count ?></div>
                <div class="stat-label">Événements à venir</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $teams_count ?></div>
                <div class="stat-label">Équipes</div>
            </div>
        </div>
        
        <div class="section">
            <h2>📌 Accès rapide</h2>
            <div class="links">
                <a href="profile.php" class="link-btn">👤 Mon profil</a>
                <a href="search.php" class="link-btn">🔍 Rechercher</a>
                <a href="teams.php" class="link-btn">👥 Équipes</a>
                <a href="index.php" class="link-btn">💬 Messages</a>
                <?php if ($current_user['username'] === 'admin'): ?>
                <a href="admin.php" class="link-btn">👑 Administration</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="section">
            <h2>📈 Activité récente</h2>
            <?php if (!empty($recent_activity)): ?>
                <?php foreach ($recent_activity as $activity): ?>
                <div class="activity-item">
                    <span><?= escape($activity['action']) ?></span>
                    <span style="color: #999;"><?= timeAgo($activity['created_at']) ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #6b7280; font-size: 13px;">Aucune activité</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
