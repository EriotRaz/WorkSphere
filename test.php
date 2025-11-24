<?php
/**
 * Script de test et diagnostic de l'installation Intranet
 * Accéder à : http://localhost/intranet/test.php
 */

$tests = [];
$passed = 0;
$failed = 0;

function test($name, $condition, $details = '') {
    global $tests, $passed, $failed;
    $status = $condition ? '✅' : '❌';
    $tests[] = ['name' => $name, 'status' => $status, 'details' => $details];
    if ($condition) $passed++; else $failed++;
}

// Tests PHP
test('PHP version ≥ 7.4', version_compare(PHP_VERSION, '7.4', '>='), 'Votre version: ' . PHP_VERSION);
test('Extension PDO MySQL', extension_loaded('pdo_mysql'), 'Obligatoire pour MySQL');
test('Extension MySQLi', extension_loaded('mysqli'), 'Recommandé');
test('Extension JSON', extension_loaded('json'), 'Pour l\'encodage JSON');
test('Extension Session', extension_loaded('session'), 'Obligatoire');
test('Extension Filter', extension_loaded('filter'), 'Pour la validation');

// Tests fichiers
test('Fichier config.php existe', file_exists('config.php'));
test('Dossier écrivable', is_writable('.'), 'Nécessaire pour les uploads');

// Tests configuration
if (file_exists('config.php')) {
    require_once 'config.php';
    
    test('Constante DB_HOST définie', defined('DB_HOST'), 'Valeur: ' . (defined('DB_HOST') ? DB_HOST : 'N/A'));
    test('Constante DB_NAME définie', defined('DB_NAME'), 'Valeur: ' . (defined('DB_NAME') ? DB_NAME : 'N/A'));
    test('Constante BASE_URL définie', defined('BASE_URL'));
    
    // Test connexion base de données
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        test('Connexion base de données', true, 'Connecté à ' . DB_NAME);
        
        // Tables
        $tables = ['users', 'messages', 'teams', 'events', 'notifications', 'audit_logs'];
        foreach ($tables as $table) {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            test("Table $table existe", $result->rowCount() > 0);
        }
        
        // Données
        $users = $pdo->query("SELECT COUNT(*) as cnt FROM users")->fetch()['cnt'];
        test('Utilisateurs en base', $users > 0, "Nombre: $users");
        
    } catch (PDOException $e) {
        test('Connexion base de données', false, $e->getMessage());
    }
}

// Tests fichiers requis
$files = [
    'login.php',
    'register.php',
    'forgot_password.php',
    'reset_password.php',
    'index.php',
    'profile.php',
    'dashboard.php',
    'search.php',
    'teams.php',
    'admin.php',
    'actions.php',
    'logout.php'
];

foreach ($files as $file) {
    test("Fichier $file présent", file_exists($file));
}

// Tests fonctionnalités
if (file_exists('config.php')) {
    require_once 'config.php';
    
    // Session
    if (!isset($_SESSION['test'])) {
        session_start();
        $_SESSION['test'] = true;
    }
    test('Sessions actives', isset($_SESSION['test']));
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tests de diagnostic - WorkSphere</title>
    <style>
        body { background: #f3f4f6; font-family: Inter, Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        header { background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%); color: #fff; padding: 40px; text-align: center; }
        h1 { margin: 0; font-size: 28px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; padding: 24px; background: #f9fafb; border-bottom: 1px solid #eef2f7; }
        .stat { text-align: center; }
        .stat-value { font-size: 28px; font-weight: 700; color: #2563eb; }
        .stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }
        .tests { padding: 24px; }
        .test-item { display: flex; align-items: center; padding: 12px; border-bottom: 1px solid #eef2f7; }
        .test-item:last-child { border-bottom: none; }
        .test-status { font-size: 20px; margin-right: 12px; width: 30px; }
        .test-info { flex: 1; }
        .test-name { font-weight: 600; color: #0f172a; }
        .test-details { font-size: 12px; color: #6b7280; margin-top: 4px; }
        .warning { background: #fef3c7; color: #b45309; }
        .error { background: #fee2e2; color: #991b1b; }
        .success { background: #e6ffed; color: #059669; }
        footer { background: #f9fafb; padding: 16px 24px; border-top: 1px solid #eef2f7; text-align: center; font-size: 12px; color: #6b7280; }
        .btn { background: #2563eb; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔍 Diagnostic WorkSphere</h1>
            <p>Vérification de l'installation et configuration</p>
        </header>
        
        <div class="stats">
            <div class="stat">
                <div class="stat-value <?= $passed > $failed ? 'style="color:#059669"' : 'style="color:#991b1b"' ?>"><?= $passed ?></div>
                <div class="stat-label">Tests réussis</div>
            </div>
            <div class="stat">
                <div class="stat-value" style="color:#ef4444"><?= $failed ?></div>
                <div class="stat-label">Tests échoués</div>
            </div>
            <div class="stat">
                <div class="stat-value"><?= count($tests) ?></div>
                <div class="stat-label">Tests total</div>
            </div>
        </div>
        
        <div class="tests">
            <h2 style="margin-top: 0; color: #2563eb;">Résultats</h2>
            <?php foreach ($tests as $test): ?>
                <div class="test-item <?= strpos($test['status'], '❌') !== false ? 'error' : 'success' ?>">
                    <div class="test-status"><?= $test['status'] ?></div>
                    <div class="test-info">
                        <div class="test-name"><?= $test['name'] ?></div>
                        <?php if ($test['details']): ?>
                            <div class="test-details"><?= $test['details'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <footer>
            <?php if ($failed === 0): ?>
                <strong style="color: #059669;">✅ Installation correcte!</strong>
                <br>
                <a href="login.php" class="btn" style="margin-top: 12px;">Aller à l'intranet</a>
            <?php else: ?>
                <strong style="color: #991b1b;">⚠️ Veuillez corriger les erreurs ci-dessus</strong>
                <br>
                <p>Consultez AMÉLIORATIONS.md pour plus d'aide</p>
            <?php endif; ?>
        </footer>
    </div>
</body>
</html>
