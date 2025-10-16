<?php
// Test de différentes configurations de connexion MySQL

$configs = [
    'root sans mot de passe' => ['user' => 'root', 'pass' => ''],
    'root avec mot de passe vide' => ['user' => 'root', 'pass' => ''],
    'root avec mot de passe root' => ['user' => 'root', 'pass' => 'root'],
    'root avec mot de passe admin' => ['user' => 'root', 'pass' => 'admin'],
];

foreach ($configs as $name => $config) {
    echo "Test: $name\n";
    try {
        $dsn = "mysql:host=127.0.0.1;port=3306;charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass']);
        echo "✅ Connexion réussie avec {$config['user']} / " . ($config['pass'] ?: 'vide') . "\n";
        
        // Test de la base de données
        $pdo->exec("USE boutique_francaise");
        echo "✅ Base de données 'boutique_francaise' accessible\n";
        break;
    } catch (PDOException $e) {
        echo "❌ Échec: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
?> 