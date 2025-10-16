<?php
// Test de connexion à la base de données
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;charset=utf8mb4',
        'root',
        ''
    );
    echo "Connexion MySQL réussie !\n";
    
    // Créer la base de données si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS boutique_francaise CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base de données 'boutique_francaise' créée ou existe déjà.\n";
    
    // Lister les bases de données
    $stmt = $pdo->query("SHOW DATABASES");
    echo "Bases de données disponibles :\n";
    while ($row = $stmt->fetch()) {
        echo "- " . $row[0] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage() . "\n";
}
?> 