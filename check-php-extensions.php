<?php
// Script pour vérifier les extensions PHP installées
echo "=== Vérification des extensions PHP ===\n";

$required_extensions = [
    'pdo',
    'pdo_mysql',
    'mysqli',
    'mbstring',
    'gd',
    'intl',
    'xml',
    'zip'
];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext: OK\n";
    } else {
        echo "❌ $ext: MANQUANT\n";
    }
}

echo "\n=== Test de connexion MySQL ===\n";
try {
    $pdo = new PDO('mysql:host=database;port=3306;dbname=boutique_prod', 'boutique', 'boutique');
    echo "✅ Connexion MySQL: OK\n";
} catch (PDOException $e) {
    echo "❌ Connexion MySQL: " . $e->getMessage() . "\n";
}

echo "\n=== Informations PHP ===\n";
echo "Version PHP: " . PHP_VERSION . "\n";
echo "Extensions chargées: " . implode(', ', get_loaded_extensions()) . "\n";
