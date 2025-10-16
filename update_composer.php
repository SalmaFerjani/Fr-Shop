<?php
/**
 * Script pour mettre à jour les dépendances Composer
 * Ce script utilise l'exécutable PHP de Laragon
 */

echo "Mise à jour des dépendances Composer...\n";

// Chemins possibles pour PHP dans Laragon
$phpPaths = [
    'C:\\laragon\\bin\\php\\php-8.2.12-Win32-vs16-x64\\php.exe',
    'C:\\laragon\\bin\\php\\php-8.1.12-Win32-vs16-x64\\php.exe',
    'C:\\laragon\\bin\\php\\php-8.0.12-Win32-vs16-x64\\php.exe',
    'C:\\xampp\\php\\php.exe',
    'C:\\wamp64\\bin\\php\\php8.2.12\\php.exe',
    'C:\\wamp64\\bin\\php\\php8.1.12\\php.exe',
];

$phpExecutable = null;

// Chercher l'exécutable PHP
foreach ($phpPaths as $path) {
    if (file_exists($path)) {
        $phpExecutable = $path;
        echo "PHP trouvé : $path\n";
        break;
    }
}

if (!$phpExecutable) {
    echo "ERREUR : Aucun exécutable PHP trouvé dans les chemins communs.\n";
    echo "Veuillez exécuter manuellement : composer update\n";
    exit(1);
}

// Exécuter composer update
$command = "\"$phpExecutable\" composer.phar update --no-dev --optimize-autoloader";
echo "Exécution : $command\n";

$output = [];
$returnCode = 0;
exec($command, $output, $returnCode);

echo "Code de retour : $returnCode\n";
echo "Sortie :\n";
foreach ($output as $line) {
    echo $line . "\n";
}

if ($returnCode === 0) {
    echo "\n✅ Mise à jour réussie !\n";
    echo "Le cache Symfony a été vidé automatiquement.\n";
} else {
    echo "\n❌ Erreur lors de la mise à jour.\n";
    echo "Veuillez exécuter manuellement : composer update\n";
}

echo "\nAppuyez sur Entrée pour continuer...";
fgets(STDIN);











