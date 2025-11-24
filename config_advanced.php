<?php
/**
 * Configuration avancée optionnelle
 * À ajouter dans config.php si souhaité
 */

// === SÉCURITÉ AVANCÉE ===

// Activer HTTPS obligatoire
// define('FORCE_HTTPS', true);

// Durée de vie des sessions (en secondes)
// define('SESSION_LIFETIME', 259200); // 72 heures

// Durée de vie du token reset password (en secondes)
// define('RESET_TOKEN_LIFETIME', 3600); // 1 heure

// Limite de tentatives de connexion
// define('LOGIN_ATTEMPTS_LIMIT', 5);
// define('LOGIN_ATTEMPTS_TIMEOUT', 900); // 15 minutes

// === RECHERCHE ===

// Nombre de résultats par page de recherche
define('SEARCH_RESULTS_LIMIT', 20);

// === PAGINATION ===

// Nombre de messages par page
define('MESSAGES_PER_PAGE', 15);

// Nombre d'événements par page
define('EVENTS_PER_PAGE', 10);

// === NOTIFICATIONS ===

// Emails
// define('ENABLE_EMAIL_NOTIFICATIONS', true);
// define('SMTP_HOST', 'smtp.gmail.com');
// define('SMTP_PORT', 587);
// define('SMTP_USER', 'your-email@gmail.com');
// define('SMTP_PASS', 'your-password');
// define('MAIL_FROM', 'noreply@intranet.local');

// === FICHIERS ===

// Dossier uploads
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');
define('MAX_UPLOAD_SIZE', 5242880); // 5 MB

// Types autorisés
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// === LOGS ===

// Activer les logs
define('ENABLE_LOGS', true);
define('LOG_DIR', __DIR__ . '/logs');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// === CACHE ===

// Activer le cache (Redis ou fichier)
// define('ENABLE_CACHE', true);
// define('CACHE_DRIVER', 'file'); // 'file' ou 'redis'
// define('CACHE_TTL', 3600); // 1 heure

// === RATE LIMITING ===

// Limiter les requêtes par minute (utile contre les attaques)
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_REQUESTS', 100); // Par minute
define('RATE_LIMIT_PER_IP', 50);

// === FONCTIONNALITÉS OPTIONNELLES ===

// 2FA (authentification double facteur)
// define('ENABLE_2FA', false);
// define('TOTP_WINDOW', 1);

// Notifications temps réel
// define('ENABLE_REALTIME_NOTIFICATIONS', false);
// define('WEBSOCKET_HOST', 'localhost');
// define('WEBSOCKET_PORT', 8080);

// Import/Export
define('ENABLE_IMPORT_EXPORT', true);

// === MODES ===

// Mode maintenance
// define('MAINTENANCE_MODE', false);
// define('MAINTENANCE_MESSAGE', 'Maintenance en cours, veuillez revenir plus tard.');

// Mode debug
define('DEBUG_MODE', false); // À mettre à true en développement

// === LOCALISATION ===

// Langue par défaut
define('DEFAULT_LANGUAGE', 'fr');

// Formats de date
define('DATE_FORMAT', 'd/m/Y');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'd/m/Y H:i');

// === PERSONNALISATION ===

// Nom de l'application
define('APP_NAME', 'WorkSphere');

// Logo
define('APP_LOGO', BASE_URL . '/assets/logo.png');

// Couleur primaire (hex)
define('PRIMARY_COLOR', '#2563eb');

// Nombre d'utilisateurs en ligne à afficher
define('ONLINE_USERS_DISPLAY_LIMIT', 10);

// === BASE DE DONNÉES AVANCÉE ===

// Charset
define('DB_CHARSET', 'utf8mb4');

// Timezone MySQL
define('DB_TIMEZONE', '+00:00');

// Pool de connexions (si applicable)
// define('DB_POOL_SIZE', 10);

// === API ===

// Activer l'API REST
// define('ENABLE_API', true);
// define('API_VERSION', 'v1');
// define('API_RATE_LIMIT', 1000); // Par jour

// Clés API
// define('API_KEY_LENGTH', 32);

// === ANALYTICS ===

// Google Analytics
// define('GA_TRACKING_ID', 'UA-XXXXXXXXX-X');

// === CDN ===

// Utiliser un CDN pour les assets
// define('CDN_URL', 'https://cdn.exemple.com');

// === SAUVEGARDES ===

// Dossier de sauvegarde
define('BACKUP_DIR', __DIR__ . '/backups');

// Fréquence de sauvegarde (en heures)
define('BACKUP_FREQUENCY', 24);

// Nombre de sauvegardes à conserver
define('BACKUP_RETENTION', 7);
?>
