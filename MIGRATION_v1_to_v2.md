# 📚 Guide de Migration v1 → v2

## Pourquoi passer à la v2 ?

La version 2.0 apporte des améliorations majeures :
- 🔐 Authentification avancée (reset password, forgotten password)
- 👤 Profils utilisateurs personnalisés
- 👥 Gestion d'équipes améliorée avec rôles
- 🔍 Recherche globale puissante
- 📊 Tableau de bord avec statistiques
- 📱 Meilleure interface utilisateur
- 🛡️ Sécurité renforcée

## Étapes de migration

### 1. Sauvegarde des données (IMPORTANT !)

```bash
# Exporter les données existantes
mysqldump -u root -p intranet_entreprise > backup_v1.sql

# Ou via phpMyAdmin
# Sélectionner base → Export → Fichier SQL
```

### 2. Créer la nouvelle base

**Option A : Nouvelle installation complète**
```bash
# Supprimer l'ancienne base (ATTENTION !)
mysql -u root -p -e "DROP DATABASE IF EXISTS intranet_entreprise;"

# Créer la nouvelle
mysql -u root -p < intranet_db_v2.sql
```

**Option B : Migrer progressivement**
```bash
# Garder l'ancienne dans une sauvegarde
mysql -u root -p -e "RENAME TABLE intranet_entreprise TO intranet_entreprise_v1;"

# Créer la nouvelle
mysql -u root -p < intranet_db_v2.sql
```

### 3. Migrer les données (si Option B)

```sql
-- Se connecter à la nouvelle BD
USE intranet_entreprise;

-- Importer les utilisateurs
INSERT INTO users (username, email, password, full_name, avatar, role, is_online, created_at)
SELECT username, email, password, full_name, avatar, 'user', is_online, created_at
FROM intranet_entreprise_v1.users
WHERE username != 'admin';

-- Importer les messages
INSERT INTO messages (user_id, content, created_at)
SELECT user_id, content, created_at
FROM intranet_entreprise_v1.messages;

-- Importer les équipes
INSERT INTO teams (name, description, created_by, created_at)
SELECT name, description, 1, created_at
FROM intranet_entreprise_v1.teams;

-- Importer les membres d'équipes
INSERT INTO team_members (team_id, user_id, role, joined_at)
SELECT team_id, user_id, 'member', joined_at
FROM intranet_entreprise_v1.team_members;

-- Importer les événements
INSERT INTO events (title, description, event_date, created_by, created_at)
SELECT title, description, event_date, created_by, created_at
FROM intranet_entreprise_v1.events;
```

### 4. Mettre à jour les fichiers

1. Télécharger tous les nouveaux fichiers :
   - `forgot_password.php`
   - `reset_password.php`
   - `profile.php`
   - `dashboard.php`
   - `search.php`
   - `teams.php`
   - `admin.php` (version améliorée)
   - `actions_v2.php` (renommer en `actions.php` après backup)
   - `intranet_db_v2.sql`
   - `AMÉLIORATIONS.md`

2. Remplacer les fichiers existants dans `C:\xampp\htdocs\intranet\`

3. **Garder** les fichiers non modifiés :
   - `config.php` (vos paramètres)
   - `logout.php`
   - `intranet_config.php`

### 5. Tester la migration

```
1. Redémarrer Apache et MySQL
2. Accéder à http://localhost/intranet/login.php
3. Se connecter avec :
   - Email: admin@entreprise.mg
   - Mot de passe: password
4. Vérifier toutes les pages
5. Tester les nouvelles fonctionnalités
```

### 6. Rôles à assigner

Après migration, assigner les rôles appropriés :

```sql
-- Admin
UPDATE users SET role = 'admin' WHERE username = 'admin';

-- Modérateurs
UPDATE users SET role = 'moderator' WHERE username IN ('laura.rh');

-- Utilisateurs normaux (défaut = 'user')
```

## Nouvelles dépendances

**Base de données :**
- Table `notifications` (pour les notifications futures)
- Table `audit_logs` (pour tracer les actions)
- Table `event_rsvp` (pour les réponses aux événements)
- Colonnes ajoutées :
  - `users.role`
  - `users.bio`
  - `users.reset_token`
  - `users.reset_expiry`
  - `messages.edited_at`
  - `teams.created_by`
  - `team_members.role`
  - `events.event_time`
  - `events.location`

## En cas de problème

### Les anciens comptes ne se connectent pas

Les mots de passe sont hachés différemment. Solution :

```sql
-- Réinitialiser les mots de passe au hash par défaut 'password'
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE role = 'user';
```

### Les messages n'apparaissent pas

Vérifier les user_id : s'ils font référence à des utilisateurs supprimés, importer avec la jointure correcte.

### Base de données en erreur

Vérifier les foreign keys :
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- ... opérations ...
SET FOREIGN_KEY_CHECKS = 1;
```

## Rollback (si problème majeur)

```bash
# Restaurer la sauvegarde
mysql -u root -p intranet_entreprise < backup_v1.sql

# Ou renommer
mysql -u root -p -e "DROP DATABASE intranet_entreprise; RENAME TABLE intranet_entreprise_v1 TO intranet_entreprise;"
```

## Support après migration

- ✅ Tous les anciens comptes restent accessibles
- ✅ Les messages, équipes, événements sont conservés
- ✅ Les rôles par défaut sont assignés
- ✅ Les données sensibles (passwords) restent hachées

## Checklist finale

- [ ] Backup effectué (`backup_v1.sql`)
- [ ] Nouvelle BD créée (`intranet_db_v2.sql`)
- [ ] Données migrées (if applicable)
- [ ] Fichiers PHP à jour
- [ ] `config.php` vérifié
- [ ] Connexion admin testée
- [ ] Profil utilisateur accessible
- [ ] Recherche fonctionnelle
- [ ] Équipes visibles
- [ ] Tableau de bord chargé

Vous êtes prêt pour la v2 ! 🚀

---

Questions ? Consultez `AMÉLIORATIONS.md`
