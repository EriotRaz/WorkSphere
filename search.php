<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$db = Database::getInstance()->getConnection();
$search = $_GET['q'] ?? '';
$type = $_GET['type'] ?? 'all';

$results = ['users' => [], 'messages' => [], 'events' => [], 'teams' => []];

if ($search) {
    $search_term = '%' . $search . '%';
    
    if ($type === 'all' || $type === 'users') {
        $results['users'] = $db->prepare("SELECT id, username, full_name, avatar FROM users WHERE username LIKE ? OR full_name LIKE ? LIMIT 10")->execute([$search_term, $search_term]) ? $db->query("SELECT id, username, full_name, avatar FROM users WHERE username LIKE '$search_term' OR full_name LIKE '$search_term' LIMIT 10")->fetchAll() : [];
    }
    
    if ($type === 'all' || $type === 'messages') {
        $stmt = $db->prepare("SELECT m.id, m.content, m.created_at, u.full_name FROM messages m JOIN users u ON m.user_id = u.id WHERE m.content LIKE ? ORDER BY m.created_at DESC LIMIT 10");
        $stmt->execute([$search_term]);
        $results['messages'] = $stmt->fetchAll();
    }
    
    if ($type === 'all' || $type === 'events') {
        $stmt = $db->prepare("SELECT id, title, event_date, location FROM events WHERE title LIKE ? OR description LIKE ? ORDER BY event_date DESC LIMIT 10");
        $stmt->execute([$search_term, $search_term]);
        $results['events'] = $stmt->fetchAll();
    }
    
    if ($type === 'all' || $type === 'teams') {
        $stmt = $db->prepare("SELECT id, name, description FROM teams WHERE name LIKE ? OR description LIKE ? LIMIT 10");
        $stmt->execute([$search_term, $search_term]);
        $results['teams'] = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche - WorkSphere</title>
    <style>
        :root { --primary: #2563eb; --dark: #0f172a; --bg: #f3f4f6; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Inter, system-ui, sans-serif; }
        body { background: var(--bg); }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .search-bar { background: #fff; padding: 24px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        input { width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 16px; }
        .filters { display: flex; gap: 8px; margin-top: 12px; }
        .filter { padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 20px; cursor: pointer; background: #f9fafb; text-decoration: none; color: var(--primary); font-size: 13px; }
        .filter.active { background: var(--primary); color: #fff; }
        .section { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .section h2 { color: var(--primary); margin-bottom: 16px; font-size: 16px; }
        .result-item { padding: 12px; border-bottom: 1px solid #eef2f7; display: flex; gap: 12px; align-items: center; }
        .result-item:last-child { border-bottom: none; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .back { display: inline-block; margin-bottom: 16px; color: var(--primary); text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back">← Retour</a>
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="q" placeholder="Rechercher utilisateurs, messages, événements, équipes..." value="<?= escape($search) ?>" autofocus>
                <div class="filters">
                    <a href="?q=<?= urlencode($search) ?>&type=all" class="filter <?= $type === 'all' ? 'active' : '' ?>">Tous</a>
                    <a href="?q=<?= urlencode($search) ?>&type=users" class="filter <?= $type === 'users' ? 'active' : '' ?>">Utilisateurs</a>
                    <a href="?q=<?= urlencode($search) ?>&type=messages" class="filter <?= $type === 'messages' ? 'active' : '' ?>">Messages</a>
                    <a href="?q=<?= urlencode($search) ?>&type=events" class="filter <?= $type === 'events' ? 'active' : '' ?>">Événements</a>
                    <a href="?q=<?= urlencode($search) ?>&type=teams" class="filter <?= $type === 'teams' ? 'active' : '' ?>">Équipes</a>
                </div>
            </form>
        </div>

        <?php if ($search): ?>
            <?php if (!empty($results['users'])): ?>
            <div class="section">
                <h2>👤 Utilisateurs (<?= count($results['users']) ?>)</h2>
                <?php foreach ($results['users'] as $u): ?>
                <div class="result-item">
                    <img class="avatar" src="<?= escape($u['avatar'] ?: AVATAR_DEFAULT) ?>">
                    <div>
                        <strong><?= escape($u['full_name']) ?></strong>
                        <div style="font-size: 12px; color: #6b7280;">@<?= escape($u['username']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($results['messages'])): ?>
            <div class="section">
                <h2>💬 Messages (<?= count($results['messages']) ?>)</h2>
                <?php foreach ($results['messages'] as $m): ?>
                <div class="result-item">
                    <div style="flex: 1;">
                        <strong><?= escape($m['full_name']) ?></strong>
                        <div style="font-size: 12px; color: #6b7280; margin-top: 4px;"><?= escape(substr($m['content'], 0, 100)) ?></div>
                        <div style="font-size: 11px; color: #999; margin-top: 4px;"><?= timeAgo($m['created_at']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($results['events'])): ?>
            <div class="section">
                <h2>📅 Événements (<?= count($results['events']) ?>)</h2>
                <?php foreach ($results['events'] as $e): ?>
                <div class="result-item">
                    <div style="flex: 1;">
                        <strong><?= escape($e['title']) ?></strong>
                        <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                            📍 <?= escape($e['location'] ?? 'Lieu TBD') ?> | 📅 <?= date('d/m/Y', strtotime($e['event_date'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($results['teams'])): ?>
            <div class="section">
                <h2>👥 Équipes (<?= count($results['teams']) ?>)</h2>
                <?php foreach ($results['teams'] as $t): ?>
                <div class="result-item">
                    <div style="flex: 1;">
                        <strong><?= escape($t['name']) ?></strong>
                        <div style="font-size: 12px; color: #6b7280; margin-top: 4px;"><?= escape(substr($t['description'] ?? 'Aucune description', 0, 60)) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (array_sum(array_map('count', $results)) === 0): ?>
            <div class="section" style="text-align: center; padding: 40px;">
                <p style="color: #6b7280; margin-bottom: 8px;">Aucun résultat trouvé pour "<?= escape($search) ?>"</p>
                <p style="font-size: 13px; color: #999;">Essayez une autre requête</p>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="section" style="text-align: center; padding: 60px 20px;">
                <p style="color: #6b7280;">Entrez un terme de recherche pour commencer</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
