<?php
// Script pour générer le hash du mot de passe
require_once 'vendor/autoload.php';

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use App\Entity\User;

// Configuration du hasher
$factory = new PasswordHasherFactory([
    User::class => ['algorithm' => 'auto']
]);

$hasher = $factory->getPasswordHasher(User::class);

// Générer le hash pour le mot de passe 'salma123'
$password = 'salma123';
$hash = $hasher->hash($password);

echo "Mot de passe: " . $password . "\n";
echo "Hash généré: " . $hash . "\n";
echo "\n";
echo "Requête SQL pour insérer l'utilisateur:\n";
echo "INSERT INTO `user` (email, roles, password, first_name, last_name, phone, address, postal_code, city, country, created_at, updated_at, is_active) VALUES ('ferjanisalma50@gmail.com', JSON_ARRAY('ROLE_USER'), '$hash', 'Salma', 'Ferjani', '24242424', 'Tunisie', '1145', 'Tunis', 'France', NOW(), NOW(), 1);\n";
