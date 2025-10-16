<?php
// Script de test pour vérifier la connexion
require_once 'vendor/autoload.php';

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\DriverManager;

// Configuration de la base de données (ajustez selon votre configuration)
$connectionParams = [
    'dbname' => 'boutique_prod', // Remplacez par votre nom de base
    'user' => 'root', // Remplacez par votre utilisateur
    'password' => '', // Remplacez par votre mot de passe
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
];

try {
    $connection = DriverManager::getConnection($connectionParams);
    
    echo "=== Test de connexion à la base de données ===\n";
    
    // Vérifier les utilisateurs existants
    $users = $connection->fetchAllAssociative('SELECT id, email, roles, password FROM `user`');
    
    echo "Utilisateurs trouvés : " . count($users) . "\n\n";
    
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Roles: " . $user['roles'] . "\n";
        echo "Password hash: " . substr($user['password'], 0, 20) . "...\n";
        echo "---\n";
    }
    
    // Vérifier l'utilisateur spécifique
    $specificUser = $connection->fetchAssociative(
        'SELECT * FROM `user` WHERE email = ?', 
        ['ferjanisalma50@gmail.com']
    );
    
    if ($specificUser) {
        echo "\n=== Utilisateur ferjanisalma50@gmail.com trouvé ===\n";
        echo "ID: " . $specificUser['id'] . "\n";
        echo "Email: " . $specificUser['email'] . "\n";
        echo "Roles: " . $specificUser['roles'] . "\n";
        echo "First Name: " . $specificUser['first_name'] . "\n";
        echo "Last Name: " . $specificUser['last_name'] . "\n";
        echo "Is Active: " . ($specificUser['is_active'] ? 'Oui' : 'Non') . "\n";
    } else {
        echo "\n❌ Utilisateur ferjanisalma50@gmail.com NON TROUVÉ\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
    echo "Vérifiez votre configuration de base de données.\n";
}
