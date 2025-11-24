# 🏢 Intranet d'Entreprise - PHP & MySQL

Application complète de communication interne pour entreprise développée avec PHP et MySQL.

## 📋 Fonctionnalités

- ✅ **Authentification** : Système de connexion et inscription sécurisés
- 📝 **Inscription** : Création de nouveaux comptes utilisateurs
- 💬 **Messagerie** : Publication et consultation de messages
- 👥 **Équipes** : Gestion des équipes de travail
- 📅 **Événements** : Calendrier des événements à venir
- 🟢 **Statut en ligne** : Visualisation des utilisateurs connectés
- 🔍 **Recherche** : Recherche dans les contenus
- 📱 **Responsive** : Interface adaptée aux mobiles

## 🚀 Installation

### Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur (ou MariaDB 10.2+)
- Serveur web (Apache, Nginx) ou PHP built-in server
- Extension PHP PDO MySQL activée

### Étapes d'installation

#### 1. Créer la base de données

Ouvrez phpMyAdmin ou votre client MySQL et exécutez le fichier `intranet.sql` :

```bash
mysql -u root -p < intranet.sql
```

Ou dans phpMyAdmin :
- Créez une nouvelle base de données nommée `intranet_entreprise`
- Importez le fichier `intranet.sql`

#### 2. Configurer l'application

Éditez le fichier `config.php` et ajustez les paramètres de connexion :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'intranet_entreprise');
define('DB_USER', 'root');          // Votre utilisateur MySQL
define('DB_PASS', '');              // Votre mot de passe MySQL
define('BASE_URL', 'http://localhost/intranet');  // URL de base
```

#### 3. Structure des fichiers

Organisez vos fichiers comme suit :

```
intranet/
├── config.php          # Configuration de l'application
├── login.php           # Page de connexion
├── register.php        # Page d'inscription (nouveau)
├── index.php           # Page principale
├── actions.php         # Traitement des actions
├── logout.php          # Déconnexion
├── intranet_db.sql    # Schéma base de données
├── intranet_config.php # Configuration supplémentaire
├── intranet_readme.md # Ce fichier
└── README.md          # Documentation de base
```

#### 4. Démarrer le serveur

**Option A : Serveur PHP intégré**
```bash
cd /chemin/vers/intranet
php -S localhost:8000
```

Accédez à : `http://localhost:8000/login.php`

**Option B : Apache/Nginx**

Placez les fichiers dans le dossier web de votre serveur :
- XAMPP : `C:\xampp\htdocs\intranet\`
- WAMP : `C:\wamp64\www\intranet\`
- MAMP : `/Applications/MAMP/htdocs/intranet/`
- Linux : `/var/www/html/intranet/`

Accédez à : `http://localhost/intranet/login.php`

## 📝 Inscription

### Créer un nouveau compte

1. Cliquez sur le lien **« Créer un compte »** sur la page de connexion
2. Remplissez le formulaire d'inscription :
   - **Nom d'utilisateur** : Identifiant unique pour l'application
   - **Nom complet** : Votre nom et prénom
   - **Email** : Adresse email valide (unique)
   - **Mot de passe** : Au moins 6 caractères recommandés
   - **Confirmer le mot de passe** : Doit correspondre au mot de passe
3. Cliquez sur **« Créer mon compte »**
4. Vous serez automatiquement connecté et redirigé vers l'accueil

### Sécurité de l'inscription

- Les mots de passe sont **hashés** avec l'algorithme `bcrypt` (PASSWORD_DEFAULT)
- Vérification de l'unicité du **nom d'utilisateur** et de l'**email**
- Validation du format **email** côté serveur
- Protection contre les **injections SQL** avec requêtes préparées

## 👤 Comptes de démonstration

Les comptes suivants sont créés automatiquement :

| Email | Nom | Mot de passe |
|-------|-----|--------------|
| sary@entreprise.mg | Sary Andria | password |
| marie@entreprise.mg | Marie Dubois | password |
| jean@entreprise.mg | Jean Martin | password |
| amin@entreprise.mg | Amin Rakoto | password |
| laura@entreprise.mg | Laura RH | password |

## 🔧 Configuration avancée

### Changer le fuseau horaire

Dans `config.php`, modifiez :
```php
date_default_timezone_set('Indian/Antananarivo');
```

### Personnaliser l'URL de base

Si votre application n'est pas à la racine, modifiez dans `config.php` :
```php
define('BASE_URL', 'http://localhost/mon-dossier/intranet');
```

### Sécuriser les mots de passe

Pour créer de nouveaux utilisateurs avec des mots de passe sécurisés :

```php
$password = password_hash('mon_mot_de_passe', PASSWORD_DEFAULT);
```

## 📊 Structure de la base de données

### Tables principales

- **users** : Utilisateurs de l'application
- **messages** : Messages publiés
- **teams** : Équipes de travail
- **team_members** : Association utilisateurs-équipes
- **events** : Événements à venir

## 🎨 Personnalisation

### Couleurs du thème

Dans `index.php` et `login.php`, modifiez les variables CSS :

```css
:root {
    --primary: #2563eb;    /* Couleur principale */
    --bg: #f3f4f6;         /* Couleur de fond */
    --dark: #0f172a;       /* Texte foncé */
}
```

## 🔐 Sécurité

L'application implémente :

- ✅ **Hachage des mots de passe** avec `password_hash()` (bcrypt)
- ✅ **Requêtes préparées** (protection SQL injection)
- ✅ **Échappement HTML** (protection XSS)
- ✅ **Sessions sécurisées**
- ✅ **Vérification d'authentification** sur chaque page
- ✅ **Validation d'inscription** : vérification doublon email/username, format email valide

### Créer un nouvel utilisateur

**Via l'interface (recommandé)** : Cliquez sur **« Créer un compte »** depuis la page de connexion (`login.php`).

**Directement en base de données** (pour test ou migration) :

```sql
INSERT INTO users (username, email, password, full_name, avatar, is_online, created_at) 
VALUES (
    'nouveau_user',
    'nouveau@exemple.fr',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Nouveau Utilisateur',
    NULL,
    FALSE,
    NOW()
);
```

(Le hash correspond au mot de passe : `password`)

## 📝 Développement futur

Fonctionnalités à ajouter :

- [ ] Gestion des documents
- [ ] Messagerie privée entre utilisateurs
- [ ] Notifications en temps réel
- [ ] Profils utilisateurs détaillés
- [ ] Upload d'images pour les messages
- [ ] Fil RSS des activités
- [ ] Export des données
- [ ] API REST

## 🐛 Dépannage

### Erreur "Cannot connect to database"

- Vérifiez les identifiants dans `config.php`
- Assurez-vous que MySQL est démarré
- Vérifiez que la base de données existe

### Erreur "Session already started"

- Supprimez les espaces avant `<?php` dans `config.php`

### Page blanche

- Activez l'affichage des erreurs PHP :
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```

### Les CSS ne s'affichent pas

- Vérifiez le `BASE_URL` dans `config.php`
- Les styles sont intégrés dans les fichiers PHP

## 📞 Support

Pour toute question ou problème :

1. Vérifiez la configuration de la base de données
2. Consultez les logs d'erreur PHP
3. Vérifiez les permissions des fichiers

## 📄 Licence

Ce projet est fourni comme exemple éducatif. Libre d'utilisation et de modification.

---

**Développé avec ❤️ à Madagascar**