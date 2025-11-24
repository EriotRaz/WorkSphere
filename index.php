<?php
require_once 'config.php';

// Vérifier la connexion
if (!isLoggedIn()) {
    redirect('/login.php');
}

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

// Récupérer les messages
$messages = $db->query("
    SELECT m.*, u.full_name, u.avatar 
    FROM messages m 
    JOIN users u ON m.user_id = u.id 
    ORDER BY m.created_at DESC 
    LIMIT 50
")->fetchAll();

// Récupérer les équipes
$teams = $db->query("
    SELECT t.*, COUNT(tm.user_id) as member_count 
    FROM teams t 
    LEFT JOIN team_members tm ON t.id = tm.team_id 
    GROUP BY t.id
")->fetchAll();

// Récupérer les événements
$events = $db->query("
    SELECT e.*, u.full_name as creator_name 
    FROM events e 
    JOIN users u ON e.created_by = u.id 
    WHERE e.event_date >= CURDATE() 
    ORDER BY e.event_date ASC
")->fetchAll();

// Récupérer les utilisateurs en ligne
$online_users = $db->query("
    SELECT * FROM users 
    WHERE is_online = TRUE AND id != $user_id 
    ORDER BY full_name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkSphere - Communication d'Entreprise</title>
    <style>
        :root{
            --bg:#f3f4f6;
            --primary:#2563eb;
            --secondary:#4f46e5;
            --dark:#0f172a;
            --muted:#6b7280;
            --radius:12px;
            --card:#ffffff;
            --shadow:0 6px 18px rgba(16,24,40,0.06);
        }
        *{box-sizing:border-box;margin:0;padding:0;font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial}
        html,body{height:100%}
        body{background:var(--bg);color:var(--dark);display:flex;min-height:100vh}

        /* Sidebar */
        .sidebar{
            width:260px;background:linear-gradient(180deg,#111827 0%, #0b1220 100%);color:#fff;padding:28px;display:flex;flex-direction:column;gap:28px;box-shadow:var(--shadow)
        }
        .sidebar h2{font-size:20px;font-weight:700;letter-spacing:0.6px}
        .nav{display:flex;flex-direction:column}
        .nav a{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;color:#cbd5e1;text-decoration:none;font-size:15px;margin-bottom:8px;transition:all .18s}
        .nav a.active,.nav a:hover{background:rgba(255,255,255,0.06);color:#fff}

        /* Main */
        .main{flex:1;display:flex;flex-direction:column;overflow:hidden}
        header{background:#fff;padding:12px 18px;border-bottom:1px solid #eef2f7;display:flex;justify-content:space-between;align-items:center}
        header .search{display:flex;gap:12px;align-items:center}
        header input{width:320px;padding:10px 14px;border-radius:10px;border:1px solid #e6edf3;font-size:14px}

        /* Content */
        .content{flex:1;display:flex;overflow:hidden}

        /* Panels */
        .panel{padding:20px;overflow-y:auto}
        .left{flex:2;background:transparent}
        .right{flex:1;background:#f9fafb;border-left:1px solid #eef2f7}

        /* Cards */
        .card{background:var(--card);padding:16px;border-radius:var(--radius);margin-bottom:16px;border:1px solid #eef2f7;box-shadow:var(--shadow)}

        /* New post */
        .composer textarea{width:100%;min-height:90px;border:1px solid #eef2f7;padding:12px;border-radius:10px;resize:vertical;font-size:14px}
        .composer .row{display:flex;justify-content:space-between;align-items:center;margin-top:10px}
        .btn{background:var(--primary);color:#fff;border:0;padding:10px 14px;border-radius:10px;cursor:pointer;font-size:14px}
        .btn.ghost{background:transparent;color:var(--primary);border:1px solid #dbeafe}

        /* Post */
        .post{display:block}
        .post-header{display:flex;align-items:center;gap:12px}
        .avatar{width:44px;height:44px;border-radius:50%;object-fit:cover;background:#cbd5e1}
        .author{font-weight:700}
        .time{font-size:12px;color:var(--muted)}
        .post-body{margin-top:10px;font-size:14px;line-height:1.5}

        /* Teams & Events */
        .list{display:flex;flex-direction:column;gap:10px}
        .team{display:flex;align-items:center;gap:12px}
        .team .meta{font-weight:600}

        /* Small utilities */
        .muted{color:var(--muted);font-size:13px}
        .hidden{display:none}
        .user-badge{display:flex;align-items:center;gap:8px;margin-bottom:10px}
        .avatar-small{width:36px;height:36px;border-radius:50%}
        .logout-btn{color:#ef4444;cursor:pointer;font-size:13px;padding:8px 12px;border-radius:6px;transition:background 0.2s}
        .logout-btn:hover{background:rgba(239,68,68,0.1)}

        @media (max-width:900px){
            .sidebar{display:none}
            header input{width:160px}
            body{flex-direction:column}
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>🌐 WorkSphere</h2>
        <nav class="nav" id="main-nav">
            <a href="#" data-view="home" class="active">🏠 Accueil</a>
            <a href="#" data-view="messages">💬 Messages</a>
            <a href="#" data-view="teams">👥 Équipes</a>
            <a href="#" data-view="events">📅 Événements</a>
            <a href="#" data-view="docs">📄 Documents</a>
        </nav>
        <div class="muted">Connecté en tant que:<br><strong><?= escape($current_user['full_name']) ?></strong></div>
        <?php if ($current_user['username'] === 'admin'): ?>
        <a href="admin.php" class="btn" style="background:#2563eb;color:#fff;display:block;margin:12px 0;text-align:center;border-radius:8px;padding:10px 0;text-decoration:none">👑 Gestion Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn">🚪 Déconnexion</a>
    </aside>

    <!-- Main -->
    <div class="main">
        <header>
            <div class="search">
                <input id="search" type="text" placeholder="Rechercher personnes, messages, événements..." />
                <div class="muted">👤 <?= escape($current_user['full_name']) ?></div>
            </div>
            <div style="font-size:13px;color:var(--muted)">Bienvenue sur WorkSphere</div>
        </header>

        <div class="content">
            <!-- Left Panel -->
            <section class="panel left">
                <!-- Home View -->
                <div id="view-home" class="view">
                    <div class="card">
                        <h3>📰 Fil d'actualités</h3>
                        <p class="muted">Bienvenue sur votre plateforme de communication interne</p>
                    </div>
                    <div class="card">
                        <h3>Activité récente</h3>
                        <?php if (!empty($messages)): ?>
                            <?php foreach (array_slice($messages, 0, 3) as $msg): ?>
                                <div style="padding:10px 0;border-bottom:1px solid #f3f4f6">
                                    <strong><?= escape($msg['full_name']) ?></strong>
                                    <span class="muted"> · <?= timeAgo($msg['created_at']) ?></span>
                                    <p style="margin-top:4px;font-size:14px"><?= escape(substr($msg['content'], 0, 100)) ?><?= strlen($msg['content']) > 100 ? '...' : '' ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="muted">Aucune activité récente</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Messages View -->
                <div id="view-messages" class="view hidden">
                    <div class="card composer">
                        <h3>✍️ Nouveau message</h3>
                        <form method="POST" action="actions.php">
                            <input type="hidden" name="action" value="add_message">
                            <textarea name="content" placeholder="Partagez quelque chose avec l'entreprise..." required></textarea>
                            <div class="row">
                                <div class="muted">Publier en tant que <strong><?= escape($current_user['full_name']) ?></strong></div>
                                <button type="submit" class="btn">Publier</button>
                            </div>
                        </form>
                    </div>

                    <div id="messages-list" class="list">
                        <?php foreach ($messages as $msg): ?>
                            <div class="card post">
                                <div class="post-header">
                                    <img class="avatar" src="<?= escape($msg['avatar'] ?: AVATAR_DEFAULT) ?>" alt="<?= escape($msg['full_name']) ?>" />
                                    <div>
                                        <div class="author"><?= escape($msg['full_name']) ?></div>
                                        <div class="time"><?= timeAgo($msg['created_at']) ?></div>
                                    </div>
                                </div>
                                <div class="post-body"><?= nl2br(escape($msg['content'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Teams View -->
                <div id="view-teams" class="view hidden">
                    <div class="card">
                        <h3>👥 Équipes</h3>
                        <p class="muted">Créez ou visualisez les équipes de travail.</p>
                        <form method="POST" action="actions.php" style="margin-top:12px;display:flex;gap:8px">
                            <input type="hidden" name="action" value="add_team">
                            <input name="name" placeholder="Nom de l'équipe" style="flex:1;padding:8px;border-radius:8px;border:1px solid #eef2f7" required />
                            <button type="submit" class="btn">Ajouter</button>
                        </form>
                    </div>

                    <div id="teams-list" class="list card" style="padding:12px">
                        <?php foreach ($teams as $team): ?>
                            <div class="team">
                                <div style="width:44px;height:44px;border-radius:8px;background:#eef2ff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px">
                                    <?= strtoupper(substr($team['name'], 0, 1)) ?>
                                </div>
                                <div class="meta">
                                    <?= escape($team['name']) ?>
                                    <div class="muted" style="font-weight:400">Membres: <?= $team['member_count'] ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Events View -->
                <div id="view-events" class="view hidden">
                    <div class="card">
                        <h3>📅 Événements à venir</h3>
                        <p class="muted">Ajoutez des événements qui intéressent l'entreprise.</p>
                        <form method="POST" action="actions.php" style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                            <input type="hidden" name="action" value="add_event">
                            <input name="title" placeholder="Titre" style="flex:2;padding:8px;border-radius:8px;border:1px solid #eef2f7" required />
                            <input name="event_date" type="date" style="padding:8px;border-radius:8px;border:1px solid #eef2f7" required />
                            <button type="submit" class="btn">Ajouter</button>
                        </form>
                    </div>

                    <div id="events-list" class="list card" style="padding:12px">
                        <?php foreach ($events as $event): ?>
                            <div class="card" style="display:flex;justify-content:space-between;align-items:center">
                                <div>
                                    <strong><?= escape($event['title']) ?></strong>
                                    <div class="muted"><?= date('d/m/Y', strtotime($event['event_date'])) ?></div>
                                </div>
                                <div class="muted"><?= daysUntil($event['event_date']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Documents View -->
                <div id="view-docs" class="view hidden card">
                    <h3>📄 Documents</h3>
                    <p class="muted">Section documents (à venir)</p>
                </div>
            </section>

            <!-- Right Panel -->
            <aside class="panel right">
                <div class="card">
                    <h3>🟢 Équipe connectée</h3>
                    <div id="online-list">
                        <?php foreach ($online_users as $user): ?>
                            <div class="user-badge">
                                <img class="avatar-small" src="<?= escape($user['avatar'] ?: AVATAR_DEFAULT) ?>" alt="<?= escape($user['full_name']) ?>" />
                                <span><?= escape($user['full_name']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card">
                    <h3>📅 Événements rapides</h3>
                    <div id="quick-events" class="muted">
                        <?php if (!empty($events)): ?>
                            <?php foreach (array_slice($events, 0, 3) as $ev): ?>
                                <div style="margin-bottom:8px">
                                    <strong><?= date('d/m', strtotime($ev['event_date'])) ?></strong> — <?= escape($ev['title']) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            Aucun événement
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <h3>ℹ️ À propos</h3>
                    <p class="muted">Plateforme WorkSphere développée avec PHP et MySQL.</p>
                </div>
            </aside>
        </div>
    </div>

    <script>
        // Navigation entre les vues
        const nav = document.getElementById('main-nav');
        nav.addEventListener('click', (e) => {
            const a = e.target.closest('a');
            if(!a || !a.dataset.view) return;
            e.preventDefault();
            const view = a.dataset.view;
            
            // Update active nav
            document.querySelectorAll('#main-nav a').forEach(x => x.classList.remove('active'));
            a.classList.add('active');
            
            // Show view
            document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
            const el = document.getElementById('view-' + view);
            if(el) el.classList.remove('hidden');
        });

        // Search functionality
        document.getElementById('search').addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.post, .team').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(query) ? '' : 'none';
            });
        });
    </script>
</body>
</html>