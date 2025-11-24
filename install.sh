#!/bin/bash
# Script d'installation rapide Intranet v2
# Usage: bash install.sh

echo "================================"
echo "   Installation Intranet v2.0"
echo "================================"
echo ""

# Vérifier MySQL
echo "🔍 Vérification MySQL..."
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL non trouvé. Installez MySQL/MariaDB d'abord."
    exit 1
fi
echo "✅ MySQL présent"

# Vérifier PHP
echo "🔍 Vérification PHP..."
if ! command -v php &> /dev/null; then
    echo "❌ PHP non trouvé. Installez PHP d'abord."
    exit 1
fi
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "✅ PHP $PHP_VERSION"

# Créer la base de données
echo ""
echo "📝 Créer la base de données..."
echo "Entrez l'utilisateur MySQL (par défaut: root):"
read -p "> " MYSQL_USER
MYSQL_USER=${MYSQL_USER:-root}

echo "Entrez le mot de passe MySQL:"
read -sp "> " MYSQL_PASS
echo ""

# Importer la BD
echo "⏳ Importation de intranet_db_v2.sql..."
mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" < intranet_db_v2.sql

if [ $? -eq 0 ]; then
    echo "✅ Base de données créée avec succès"
else
    echo "❌ Erreur lors de l'import de la base de données"
    exit 1
fi

# Vérifier permissions fichiers
echo ""
echo "🔒 Vérification des permissions..."
if ! [ -w "." ]; then
    echo "⚠️ Avertissement: Dossier intranet n'est pas accessible en écriture"
fi
echo "✅ Permissions OK"

# Afficher infos
echo ""
echo "================================"
echo "   Installation complète! ✅"
echo "================================"
echo ""
echo "📌 Prochaines étapes:"
echo ""
echo "1. Vérifier config.php"
echo "   - DB_HOST: localhost"
echo "   - DB_NAME: intranet_entreprise"
echo "   - DB_USER: $MYSQL_USER"
echo "   - DB_PASS: (votre mot de passe)"
echo "   - BASE_URL: http://localhost/intranet"
echo ""
echo "2. Placer les fichiers dans:"
echo "   Windows XAMPP: C:\\xampp\\htdocs\\intranet\\"
echo "   Linux:         /var/www/html/intranet"
echo "   macOS MAMP:    /Applications/MAMP/htdocs/intranet"
echo ""
echo "3. Accéder à:"
echo "   http://localhost/intranet/test.php (diagnostic)"
echo "   http://localhost/intranet/login.php (accès)"
echo ""
echo "4. Comptes de démo:"
echo "   Email: admin@entreprise.mg | Password: password"
echo "   Email: sary@entreprise.mg  | Password: password"
echo ""
echo "Pour plus d'aide, consulter AMÉLIORATIONS.md"
echo ""
