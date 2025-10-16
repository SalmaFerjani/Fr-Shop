<?php
// Script pour tester la configuration de sécurité
require_once 'vendor/autoload.php';

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use App\Entity\User;

echo "=== Test de la configuration de sécurité ===\n\n";

try {
    // Configuration du hasher (même que dans Symfony)
    $factory = new PasswordHasherFactory([
        User::class => ['algorithm' => 'auto']
    ]);
    $hasher = $factory->getPasswordHasher(User::class);
    
    // Créer un utilisateur de test
    $user = new User();
    $user->setEmail('test@example.com');
    $user->setRoles(['ROLE_USER']);
    
    // Tester le hachage
    $password = 'salma123';
    $hash = $hasher->hash($password);
    
    echo "Mot de passe: " . $password . "\n";
    echo "Hash généré: " . $hash . "\n\n";
    
    // Tester la validation
    $user->setPassword($hash);
    $isValid = $hasher->isPasswordValid($user, $password);
    
    echo "Test de validation: " . ($isValid ? "✅ VALIDE" : "❌ INVALIDE") . "\n\n";
    
    // Tester avec différents algorithmes
    echo "=== Test avec différents algorithmes ===\n";
    
    $algorithms = ['auto', 'bcrypt', 'argon2i', 'argon2id'];
    
    foreach ($algorithms as $algorithm) {
        $factory = new PasswordHasherFactory([
            User::class => ['algorithm' => $algorithm]
        ]);
        $hasher = $factory->getPasswordHasher(User::class);
        
        $hash = $hasher->hash($password);
        $isValid = $hasher->isPasswordValid($user, $password);
        
        echo "Algorithme: " . $algorithm . " - " . ($isValid ? "✅ VALIDE" : "❌ INVALIDE") . "\n";
        echo "Hash: " . substr($hash, 0, 30) . "...\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
