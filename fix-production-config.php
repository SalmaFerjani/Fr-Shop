<?php
/**
 * Script pour corriger la configuration de production
 * À exécuter sur votre serveur d'hébergement
 */

echo "=== Script de Correction de Configuration de Production ===\n\n";

// 1. Créer le fichier .env.prod
$envProdContent = '###> symfony/framework-bundle ###
APP_ENV=prod
APP_SECRET=fb0fffcb0bcb71d13d49867ef470e719
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
# IMPORTANT: Remplacez par vos vraies informations de base de données
DATABASE_URL="mysql://username:password@localhost:3306/boutique_francaise?serverVersion=8.0&charset=utf8mb4"
###< doctrine/doctrine-bundle ###

###> symfony/messenger ###
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
###< symfony/messenger ###

###> symfony/mailer ###
MAILER_DSN=smtp://localhost:25
###< symfony/mailer ###';

if (file_put_contents('.env.prod', $envProdContent)) {
    echo "✅ Fichier .env.prod créé\n";
} else {
    echo "❌ Erreur lors de la création du fichier .env.prod\n";
}

// 2. Vérifier les permissions
$directories = ['var/', 'public/'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        chmod($dir, 0755);
        echo "✅ Permissions définies pour $dir\n";
    }
}

// 3. Vérifier la structure des fichiers
$requiredFiles = [
    'public/.htaccess',
    'public/index.php',
    'config/bundles.php',
    'composer.json'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ Fichier $file présent\n";
    } else {
        echo "❌ Fichier $file manquant\n";
    }
}

// 4. Instructions pour l'utilisateur
echo "\n=== INSTRUCTIONS IMPORTANTES ===\n";
echo "1. Modifiez le fichier .env.prod avec vos vraies informations de base de données\n";
echo "2. Remplacez 'username', 'password', 'localhost' par vos vraies valeurs\n";
echo "3. Exécutez: composer install --no-dev --optimize-autoloader\n";
echo "4. Exécutez: php bin/console cache:clear --env=prod\n";
echo "5. Exécutez: php bin/console doctrine:migrations:migrate --env=prod\n";
echo "6. Vérifiez que votre serveur web pointe vers le répertoire 'public/'\n\n";

echo "=== Configuration Terminée ===\n";
?>
