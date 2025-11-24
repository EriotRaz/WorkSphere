<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Récupérer les équipes de l'utilisateur
$stmt = $db->prepare("
    SELECT DISTINCT t.*, COUNT(tm.user_id) as member_count, u.full_name as creator_name
    FROM teams t
    LEFT JOIN team_members tm ON t.id = tm.team_id
    LEFT JOIN users u ON t.created_by = u.id
    GROUP BY t.id
    ORDER BY t.created_at DESC
");
$stmt->execute();
$teams = $stmt->fetchAll();

$success = '';
$error = '';

// Gestion des équipes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_team') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (!$name) {
            $error = 'Le nom de l\'équipe est obligatoire';
        } else {
            $stmt = $db->prepare("INSERT INTO teams (name, description, created_by) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $user_id]);
            $team_id = $db->lastInsertId();
            
            $stmt = $db->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, 'leader')");
            $stmt->execute([$team_id, $user_id]);
            
            $success = 'Équipe créée avec succès';
        }
    }
    
    if ($action === 'add_member') {
        $team_id = intval($_POST['team_id']);
        $member_id = intval($_POST['member_id']);
        
        $stmt = $db->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, 'member')");
        $stmt->execute([$team_id, $member_id]);
        $success = 'Membre ajouté';
    }
    
    if ($action === 'remove_member') {
        $team_id = intval($_POST['team_id']);
        $member_id = intval($_POST['member_id']);
        
        $stmt = $db->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
        $stmt->execute([$team_id, $member_id]);
        $success = 'Membre retiré';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Équipes - WorkSphere</title>
    <style>
        :root { --primary: #2563eb; --dark: #0f172a; --bg: #f3f4f6; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Inter, system-ui, sans-serif; }
        body { background: var(--bg); padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .back { display: inline-block; margin-bottom: 16px; color: var(--primary); text-decoration: none; font-size: 13px; }
        .header { background: #fff; padding: 24px; border-radius: 12px; margin-bottom: 24px; }
        h1 { color: var(--dark); margin-bottom: 16px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; font-weight: 600; color: var(--dark); font-size: 13px; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 13px; }
        .btn { background: var(--primary); color: #fff; border: 0; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; }
        .success { background: #e6ffed; color: #059669; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .error { background: #fee; color: #a00; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .teams-grid { display: grid; gap: 20px; }
        .team-card { background: #fff; padding: 20px; border-radius: 12px; }
        .team-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; }
        .team-name { font-size: 18px; font-weight: 700; color: var(--primary); }
        .team-meta { font-size: 12px; color: #6b7280; }
        .members { margin-top: 16px; border-top: 1px solid #eef2f7; padding-top: 12px; }
        .member { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back">← Retour</a>
        
        <div class="header">
            <h1>👥 Gestion des équipes</h1>
            
            <?php if ($success): ?><div class="success"><?= escape($success) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="error"><?= escape($error) ?></div><?php endif; ?>
            
            <h3 style="margin-bottom: 12px; font-size: 14px;">Créer une équipe</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_team">
                <div class="form-group">
                    <label>Nom de l'équipe</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="2"></textarea>
                </div>
                <button type="submit" class="btn">Créer</button>
            </form>
        </div>
        
        <div class="teams-grid">
            <?php foreach ($teams as $team): ?>
            <div class="team-card">
                <div class="team-header">
                    <div>
                        <div class="team-name"><?= escape($team['name']) ?></div>
                        <div class="team-meta">Créée par <?= escape($team['creator_name']) ?></div>
                    </div>
                </div>
                <?php if ($team['description']): ?>
                    <p style="font-size: 13px; color: #6b7280; margin-bottom: 12px;"><?= escape($team['description']) ?></p>
                <?php endif; ?>
                
                <div class="members">
                    <strong style="font-size: 12px; color: #6b7280;">Membres (<?= $team['member_count'] ?>)</strong>
                    <?php
                    $stmt = $db->prepare("SELECT u.id, u.full_name, tm.role FROM team_members tm JOIN users u ON tm.user_id = u.id WHERE tm.team_id = ?");
                    $stmt->execute([$team['id']]);
                    $members = $stmt->fetchAll();
                    ?>
                    <?php foreach ($members as $m): ?>
                    <div class="member">
                        <span><?= escape($m['full_name']) ?> <span style="color: #999;">(<?= $m['role'] ?>)</span></span>
                        <?php if ($team['created_by'] === $user_id && $m['id'] !== $user_id): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="remove_member">
                                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                <input type="hidden" name="member_id" value="<?= $m['id'] ?>">
                                <button type="submit" style="background: #ef4444; color: #fff; border: 0; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">✕</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
