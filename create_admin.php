<?php
// Script rapide pour créer un administrateur
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
    echo "=== Création d'un compte administrateur ===\n\n";
    
    // Connexion à la base de données
    $connection = DriverManager::getConnection($connectionParams);
    
    // Configuration du hasher
    $factory = new PasswordHasherFactory([
        User::class => ['algorithm' => 'auto']
    ]);
    $hasher = $factory->getPasswordHasher(User::class);
    
    // Informations de l'administrateur
    $adminEmail = 'admin@boutiquefrancaise.fr';
    $adminPassword = 'admin123';
    $adminFirstName = 'Admin';
    $adminLastName = 'Boutique';
    
    // Créer un objet User temporaire pour le hachage
    $tempUser = new User();
    $tempUser->setEmail($adminEmail);
    
    // Générer le hash du mot de passe
    $hashedPassword = $hasher->hash($adminPassword);
    
    echo "Email: " . $adminEmail . "\n";
    echo "Mot de passe: " . $adminPassword . "\n";
    echo "Hash généré: " . $hashedPassword . "\n\n";
    
    // Vérifier si l'utilisateur existe déjà
    $existingUser = $connection->fetchAssociative(
        'SELECT id FROM `user` WHERE email = ?', 
        [$adminEmail]
    );
    
    if ($existingUser) {
        echo "⚠️ Utilisateur existant trouvé, mise à jour...\n";
        
        // Mettre à jour l'utilisateur existant
        $connection->update('`user`', [
            'roles' => JSON_ARRAY('ROLE_ADMIN'),
            'password' => $hashedPassword,
            'first_name' => $adminFirstName,
            'last_name' => $adminLastName,
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $existingUser['id']]);
        
        echo "✅ Utilisateur existant mis à jour avec les privilèges administrateur !\n";
        
    } else {
        echo "❌ Utilisateur non trouvé, création...\n";
        
        // Insérer un nouvel utilisateur administrateur
        $connection->insert('`user`', [
            'email' => $adminEmail,
            'roles' => JSON_ARRAY('ROLE_ADMIN'),
            'password' => $hashedPassword,
            'first_name' => $adminFirstName,
            'last_name' => $adminLastName,
            'phone' => '+33 1 23 45 67 89',
            'address' => '123 Rue de la Mode',
            'postal_code' => '75001',
            'city' => 'Paris',
            'country' => 'France',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ]);
        
        echo "✅ Utilisateur administrateur créé avec succès !\n";
    }
    
    // Vérifier la création
    $adminUser = $connection->fetchAssociative(
        'SELECT id, email, roles, first_name, last_name FROM `user` WHERE email = ?', 
        [$adminEmail]
    );
    
    if ($adminUser) {
        echo "\n=== Vérification ===\n";
        echo "ID: " . $adminUser['id'] . "\n";
        echo "Email: " . $adminUser['email'] . "\n";
        echo "Roles: " . $adminUser['roles'] . "\n";
        echo "Nom: " . $adminUser['first_name'] . " " . $adminUser['last_name'] . "\n";
        
        // Tester la validation du mot de passe
        $testUser = new User();
        $testUser->setEmail($adminEmail);
        $testUser->setPassword($hashedPassword);
        
        $isValid = $hasher->isPasswordValid($testUser, $adminPassword);
        echo "Test de validation: " . ($isValid ? "✅ VALIDE" : "❌ INVALIDE") . "\n";
    }
    
    echo "\n=== Informations de connexion administrateur ===\n";
    echo "URL d'administration: /admin\n";
    echo "Email: " . $adminEmail . "\n";
    echo "Mot de passe: " . $adminPassword . "\n";
    echo "Rôles: ROLE_ADMIN\n";
    
    echo "\n🎉 Compte administrateur prêt !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "Vérifiez votre configuration de base de données.\n";
}
