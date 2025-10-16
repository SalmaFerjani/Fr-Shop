<?php
// Script pour corriger le hash du mot de passe
require_once 'vendor/autoload.php';

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use App\Entity\User;
use Doctrine\DBAL\DriverManager;

// Configuration de la base de données
$connectionParams = [
    'dbname' => 'boutique_prod',
    'user' => 'root',
    'password' => '', // Ajustez selon votre configuration
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
];

try {
    echo "=== Correction du hash du mot de passe ===\n";
    
    // Connexion à la base de données
    $connection = DriverManager::getConnection($connectionParams);
    
    // Configuration du hasher
    $factory = new PasswordHasherFactory([
        User::class => ['algorithm' => 'auto']
    ]);
    $hasher = $factory->getPasswordHasher(User::class);
    
    // Créer un objet User temporaire pour le hachage
    $tempUser = new User();
    $tempUser->setEmail('ferjanisalma50@gmail.com');
    
    // Générer le hash correct
    $password = 'salma123';
    $correctHash = $hasher->hash($password);
    
    echo "Mot de passe original: " . $password . "\n";
    echo "Nouveau hash généré: " . $correctHash . "\n\n";
    
    // Vérifier l'utilisateur actuel
    $user = $connection->fetchAssociative(
        'SELECT id, email, password FROM `user` WHERE email = ?', 
        ['ferjanisalma50@gmail.com']
    );
    
    if ($user) {
        echo "Utilisateur trouvé:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Hash actuel: " . $user['password'] . "\n\n";
        
        // Mettre à jour le hash
        $connection->update('`user`', 
            ['password' => $correctHash], 
            ['id' => $user['id']]
        );
        
        echo "✅ Hash du mot de passe mis à jour avec succès !\n";
        
        // Vérifier que la mise à jour a fonctionné
        $updatedUser = $connection->fetchAssociative(
            'SELECT id, email, password FROM `user` WHERE email = ?', 
            ['ferjanisalma50@gmail.com']
        );
        
        echo "\nVérification:\n";
        echo "Nouveau hash en base: " . $updatedUser['password'] . "\n";
        
        // Tester la validation du mot de passe
        $testUser = new User();
        $testUser->setEmail('ferjanisalma50@gmail.com');
        $testUser->setPassword($updatedUser['password']);
        
        $isValid = $hasher->isPasswordValid($testUser, 'salma123');
        echo "Test de validation: " . ($isValid ? "✅ VALIDE" : "❌ INVALIDE") . "\n";
        
    } else {
        echo "❌ Utilisateur non trouvé !\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "Vérifiez votre configuration de base de données.\n";
}
