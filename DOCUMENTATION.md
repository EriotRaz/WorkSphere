# 📚 Documentation Intranet v2.0

Bienvenue dans la documentation complète de l'**Intranet d'Entreprise v2.0**. 
Voici tous les documents disponibles pour vous aider.

## 📖 Guides de démarrage

### 🚀 Premiers pas
- **[RÉSUMÉ_COMPLET.md](RÉSUMÉ_COMPLET.md)** - Vue d'ensemble de tous les changements
- **[AMÉLIORATIONS.md](AMÉLIORATIONS.md)** - Guide détaillé des nouvelles fonctionnalités
- **[MIGRATION_v1_to_v2.md](MIGRATION_v1_to_v2.md)** - Guide de migration depuis v1
- **[CHANGELOG.md](CHANGELOG.md)** - Historique des changements et versions

## 🔧 Configuration

### Installation
1. Importer la base : `mysql -u root -p < intranet_db_v2.sql`
2. Vérifier `config.php` avec vos paramètres
3. Placer les fichiers dans votre dossier web
4. Accéder à `http://localhost/intranet/test.php`

### Configurations avancées
- **[config_advanced.php](config_advanced.php)** - Options de configuration optionnelles
- **[test.php](test.php)** - Script de diagnostic installation

## 🎯 Pages de l'application

### Authentification (Public)
- `login.php` - Connexion utilisateur
- `register.php` - Créer un compte
- `forgot_password.php` - Demander reset password
- `reset_password.php` - Compléter le reset

### Utilisateurs (Connecté)
- `profile.php` - Profil personnel
- `dashboard.php` - Vue d'ensemble
- `admin.php` - Administration (admin only)

### Contenu (Connecté)
- `index.php` - Accueil (messages, équipes, événements)
- `teams.php` - Gestion des équipes
- `search.php` - Recherche globale

### Système
- `logout.php` - Déconnexion
- `actions.php` - Traitement des actions
- `config.php` - Configuration principale

## 🔐 Comptes de démonstration

```
Admin
  Email: admin@entreprise.mg
  Mot de passe: password
  Rôle: admin (accès à tout)

Modérateur
  Email: laura@entreprise.mg
  Mot de passe: password
  Rôle: moderator

Utilisateurs normaux
  Email: sary@entreprise.mg
  Email: marie@entreprise.mg
  Email: jean@entreprise.mg
  Email: amin@entreprise.mg
  Mot de passe: password (pour tous)
  Rôle: user
```

## 📊 Structure de données

### Tables principales
- `users` - Utilisateurs avec rôles
- `messages` - Messages avec édition
- `teams` - Équipes
- `team_members` - Membres d'équipes
- `events` - Événements
- `event_rsvp` - RSVP événements
- `notifications` - Notifications
- `audit_logs` - Logs administrateur

### Relations
- user → messages (1 to many)
- user → teams (many to many via team_members)
- user → events (created_by)
- team → team_members (1 to many)
- event → event_rsvp (1 to many)

## 🔧 Développement

### PHP
- Version minimum: 7.4
- Extensions: PDO MySQL, Session, JSON, Filter
- Framework: Custom MVC simple

### Base de données
- MySQL 5.7+ ou MariaDB 10.2+
- Charset: utf8mb4
- Timezone: Configurable

### Frontend
- HTML5 sémantique
- CSS3 (Flexbox, Grid)
- JavaScript vanilla (sans framework)
- Mobile-first responsive

## 🚀 Fonctionnalités principales

### Authentification
- [x] Inscription sécurisée
- [x] Connexion avec session
- [x] Mot de passe oublié
- [x] Reset password sécurisé
- [x] Logout propre

### Profil
- [x] Affichage profil
- [x] Édition bio
- [x] Changement mot de passe
- [x] Statistiques utilisateur

### Messages
- [x] Publier messages
- [x] Éditer ses messages
- [x] Supprimer ses messages
- [x] Affichage "édité"
- [x] Recherche messages

### Équipes
- [x] Créer équipes
- [x] Ajouter membres
- [x] Supprimer membres
- [x] Rôles (leader, member)
- [x] Vue équipes

### Événements
- [x] Créer événements
- [x] Heure + lieu
- [x] Système RSVP
- [x] Vue événements
- [x] Affichage participants

### Recherche
- [x] Recherche utilisateurs
- [x] Recherche messages
- [x] Recherche événements
- [x] Recherche équipes
- [x] Filtres par type

### Administration
- [x] CRUD utilisateurs
- [x] Assignation rôles
- [x] Protection admin
- [x] Suppression comptes
- [x] Édition données

### Tableau de bord
- [x] Statistiques globales
- [x] Activité récente
- [x] Accès rapide
- [x] Compteurs

## 🛠️ Utilitaires

### Diagnostic
```
http://localhost/intranet/test.php
```
Vérifie installation, DB, permissions

### Données de test
Déjà incluses dans `intranet_db_v2.sql`
- 6 utilisateurs
- 2 messages
- 3 équipes
- 3 événements

## 📝 Conventions

### Nommage
- Classes: PascalCase
- Fonctions: snake_case
- Variables: camelCase
- Constantes: UPPER_CASE

### Structure fichiers
- Config en `config.php`
- Database en `intranet_db_v2.sql`
- Pages publiques: à la racine
- Styles: inline (simplicité)
- Scripts: inline ou vanilla JS

### Sécurité
- Requêtes préparées obligatoires
- Échappement HTML avec `escape()`
- Vérification authentification sur chaque page
- Hachage bcrypt pour tous les mots de passe

## ❓ FAQ

**Q: Comment changer la couleur primaire?**
A: Modifier `--primary: #2563eb` dans le CSS de chaque fichier

**Q: Comment ajouter un nouvel utilisateur?**
A: Aller à Admin → Ajouter utilisateur

**Q: Comment réinitialiser un mot de passe?**
A: Utilisateur: Accès Mot de passe oublié
   Admin: Modifier user dans Admin

**Q: Comment exporter les données?**
A: Via phpMyAdmin → Export SQL

**Q: Comment sauvegarder?**
A: `mysqldump -u root -p intranet_entreprise > backup.sql`

## 🐛 Dépannage

### Erreur connexion
1. Vérifier `config.php` identifiants
2. Vérifier MySQL démarré
3. Vérifier DB existe

### Page blanche
1. Vérifier `BASE_URL` dans `config.php`
2. Vérifier permissions fichiers
3. Consulter PHP error logs

### CSRF/Session
1. Vérifier cookies activés
2. Vérifier session.save_path
3. Redémarrer Apache

Voir **[MIGRATION_v1_to_v2.md](MIGRATION_v1_to_v2.md#en-cas-de-problème)** pour plus

## 📞 Support

Consultez les fichiers appropriés :
- Installation: `AMÉLIORATIONS.md`
- Migration: `MIGRATION_v1_to_v2.md`
- Changements: `CHANGELOG.md`
- Diagnostic: `test.php`

## 📚 Documentation externe

- [PHP PDO](https://www.php.net/manual/en/book.pdo.php)
- [MySQL Docs](https://dev.mysql.com/doc/)
- [HTML5](https://developer.mozilla.org/en-US/docs/Web/HTML)
- [CSS3](https://developer.mozilla.org/en-US/docs/Web/CSS)
- [JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

## 📄 Licence

Application éducationnelle - Libre d'utilisation et modification

---

**Version:** 2.0
**Dernière mise à jour:** Novembre 2025
**Statut:** ✅ Production-ready

**Pour commencer:** Consulter [RÉSUMÉ_COMPLET.md](RÉSUMÉ_COMPLET.md)
