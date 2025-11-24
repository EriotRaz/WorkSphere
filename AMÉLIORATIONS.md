## 🏢 Intranet d'Entreprise - Améliorations complètes

Application complète et moderne de communication interne avec authentification avancée, gestion utilisateurs, messagerie améliorée, équipes, événements et bien plus.

### 🎯 Nouvelles fonctionnalités

**Authentification & Sécurité**
- ✅ Inscription utilisateurs sécurisée (hachage bcrypt)
- ✅ Mot de passe oublié (lien de réinitialisation)
- ✅ Réinitialisation de mot de passe sécurisée
- ✅ Vérification email unique
- ✅ Sessions sécurisées

**Gestion Utilisateurs**
- ✅ Système de rôles (admin, moderator, user)
- ✅ Profil utilisateur personnalisé (bio, avatar)
- ✅ Modification de profil
- ✅ Changement de mot de passe
- ✅ Gestion complète des utilisateurs (admin)

**Messagerie Avancée**
- ✅ Publication de messages
- ✅ Édition des propres messages
- ✅ Suppression des messages (auteur ou admin)
- ✅ Affichage du statut "édité"
- ✅ Recherche dans les messages

**Gestion des Équipes**
- ✅ Création d'équipes par utilisateurs
- ✅ Ajout/suppression de membres
- ✅ Rôles dans les équipes (leader, member)
- ✅ Vue détaillée des équipes
- ✅ Description des équipes

**Événements Améliorés**
- ✅ Création d'événements avec date/heure/lieu
- ✅ Système RSVP (Oui/Non/Peut-être)
- ✅ Affichage des participants

**Recherche Globale**
- ✅ Recherche unifiée (utilisateurs, messages, événements, équipes)
- ✅ Filtres par type
- ✅ Autocomplete et suggestions

**Tableau de Bord**
- ✅ Vue d'ensemble des statistiques
- ✅ Activité récente
- ✅ Accès rapide aux fonctionnalités
- ✅ Personnalisé par rôle

**Administration**
- ✅ CRUD utilisateurs complet
- ✅ Gestion des rôles
- ✅ Suppression d'utilisateurs (sauf admin)
- ✅ Protection de l'admin principal

### 📋 Pages disponibles

| Page | URL | Description |
|------|-----|-------------|
| Accueil | `/index.php` | Fil d'actualités, équipes, événements |
| Profil | `/profile.php` | Profil personnalisé, modification, sécurité |
| Tableau de bord | `/dashboard.php` | Vue d'ensemble et statistiques |
| Équipes | `/teams.php` | Gestion complète des équipes |
| Recherche | `/search.php` | Recherche globale et filtres |
| Admin | `/admin.php` | Gestion utilisateurs CRUD |
| Connexion | `/login.php` | Authentification |
| Inscription | `/register.php` | Créer un compte |
| Mot de passe oublié | `/forgot_password.php` | Réinitialisation |
| Réinitialiser password | `/reset_password.php` | Compléter la réinitialisation |
| Déconnexion | `/logout.php` | Fermer la session |

### 🗄️ Schéma base de données v2

**Tables principales**
- `users` - Utilisateurs (avec role, bio, reset_token)
- `messages` - Messages (avec edited_at)
- `teams` - Équipes (avec created_by)
- `team_members` - Membres d'équipes (avec rôles)
- `events` - Événements (avec heure, lieu)
- `event_rsvp` - RSVP des événements
- `notifications` - Notifications utilisateurs
- `audit_logs` - Logs d'administration

**Utiliser la nouvelle version de la DB :**
```bash
mysql -u root -p intranet_entreprise < intranet_db_v2.sql
```

### 👥 Rôles et Permissions

**Admin**
- Accès à `/admin.php`
- Gestion complète des utilisateurs
- Suppression de contenu
- Modération

**Moderator**
- Création d'équipes et événements
- Modération limitée
- Pas d'accès admin

**User (défaut)**
- Création de messages
- Création d'équipes
- RSVP événements

### 🔐 Comptes de démonstration (v2)

| Email | Nom | Rôle | Mot de passe |
|-------|-----|------|--------------|
| admin@entreprise.mg | Administrateur | admin | password |
| sary@entreprise.mg | Sary Andria | user | password |
| marie@entreprise.mg | Marie Dubois | user | password |
| jean@entreprise.mg | Jean Martin | user | password |
| amin@entreprise.mg | Amin Rakoto | user | password |
| laura@entreprise.mg | Laura RH | moderator | password |

### 🚀 Installation (version améliorée)

1. **Importer la BD v2**
```bash
mysql -u root -p < intranet_db_v2.sql
```

2. **Vérifier config.php**
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'intranet_entreprise');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/intranet');
```

3. **Placer les fichiers** dans `C:\xampp\htdocs\intranet\`

4. **Accéder** à `http://localhost/intranet/login.php`

### 📁 Structure complète

```
intranet/
├── config.php              # Configuration de base
├── login.php               # Page de connexion
├── register.php            # Inscription
├── forgot_password.php     # Mot de passe oublié
├── reset_password.php      # Réinitialiser password
├── logout.php              # Déconnexion
├── index.php               # Accueil (messages, équipes, événements)
├── profile.php             # Profil utilisateur
├── dashboard.php           # Tableau de bord
├── teams.php               # Gestion équipes
├── search.php              # Recherche globale
├── admin.php               # Administration
├── actions.php             # Traitement actions (CRUD)
├── actions_v2.php          # Actions étendues
├── intranet_db.sql         # Schéma original
├── intranet_db_v2.sql      # Schéma amélioré (à utiliser)
├── intranet_config.php     # Config additionnelle
└── README.md               # Documentation
```

### 🎨 Améliorations UI/UX

- ✨ Design moderne et professionnel
- 📱 Responsive sur mobile/tablette
- 🎯 Navigation intuitive
- ⚡ Chargement rapide
- 🎨 Couleurs cohérentes
- ♿ Accessible

### 🔒 Sécurité

- ✅ Hachage bcrypt (PASSWORD_DEFAULT)
- ✅ Requêtes préparées (anti-SQL injection)
- ✅ Échappement HTML (anti-XSS)
- ✅ Sessions sécurisées
- ✅ Protection des pages privées
- ✅ CSRF tokens (à ajouter)
- ✅ Token de réinitialisation temporaire (1h)

### 🛠️ Configuration avancée

**Changer le fuseau horaire** (config.php)
```php
date_default_timezone_set('Europe/Paris');
```

**Augmenter le timeout session** (config.php)
```php
define('SESSION_LIFETIME', 259200); // 72 heures
```

**Personnaliser avatar par défaut**
```php
define('AVATAR_DEFAULT', 'https://i.pravatar.cc/150?u=default');
```

### 📊 Statistiques disponibles

- Nombre total d'utilisateurs
- Nombre de messages
- Événements à venir
- Nombre d'équipes
- Activité récente
- Utilisateurs en ligne

### 📝 Fonctionnalités futures possibles

- [ ] Notifications en temps réel (WebSocket)
- [ ] Messagerie privée 1-to-1
- [ ] Partage de fichiers
- [ ] Mentions (@username)
- [ ] Hashtags et catégories
- [ ] Likes et commentaires
- [ ] Export des données
- [ ] API REST
- [ ] Mode sombre
- [ ] Intégration Slack/Teams
- [ ] 2FA (authentification double facteur)
- [ ] Audit logs complets

### 🐛 Dépannage

**Erreur de connexion base**
- Vérifier identifiants `config.php`
- Vérifier MySQL démarré
- Vérifier base de données existe

**Page blanche**
- Vérifier `BASE_URL` dans `config.php`
- Vérifier permissions fichiers
- Activer affichage erreurs PHP

**Impossible de se connecter**
- Vérifier comptes dans `intranet_db_v2.sql`
- Essayer avec `admin` / `password`
- Vérifier cookies activés

### 📞 Support

Pour toute question :
1. Vérifier la configuration
2. Consulter les logs PHP/MySQL
3. Vérifier les permissions fichiers
4. Tester avec les comptes de démo

### 📄 Licence

Projet éducatif - Libre d'utilisation et modification

---

**Dernière mise à jour:** Novembre 2025
**Version:** 2.0 (Amélioration complète)
**Développé avec ❤️**
