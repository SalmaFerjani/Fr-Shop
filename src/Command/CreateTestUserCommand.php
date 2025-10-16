<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-user',
    description: 'Crée un utilisateur de test pour la connexion',
)]
class CreateTestUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('👤 Création d\'un utilisateur de test');

        try {
            // Vérifier si l'utilisateur existe déjà
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'ferjanisalma50@gmail.com']);
            
            if ($existingUser) {
                $io->text('✅ Utilisateur existant trouvé : ' . $existingUser->getEmail());
                
                // Vérifier les rôles
                $roles = $existingUser->getRoles();
                $io->text('📋 Rôles actuels : ' . implode(', ', $roles));
                
                // Vérifier si le mot de passe est correct
                $isPasswordValid = $this->passwordHasher->isPasswordValid($existingUser, 'salma123');
                if ($isPasswordValid) {
                    $io->success('✅ Mot de passe valide !');
                } else {
                    $io->warning('⚠️ Mot de passe invalide, mise à jour...');
                    $existingUser->setPassword($this->passwordHasher->hashPassword($existingUser, 'salma123'));
                    $this->entityManager->persist($existingUser);
                }
                
                // S'assurer que les rôles sont corrects
                if (empty($roles) || !in_array('ROLE_USER', $roles)) {
                    $io->warning('⚠️ Correction des rôles...');
                    $existingUser->setRoles(['ROLE_USER']);
                    $this->entityManager->persist($existingUser);
                }
                
            } else {
                $io->text('❌ Utilisateur non trouvé, création...');
                
                // Créer un nouvel utilisateur
                $user = new User();
                $user->setEmail('ferjanisalma50@gmail.com')
                     ->setFirstName('Salma')
                     ->setLastName('Ferjani')
                     ->setPhone('24242424')
                     ->setAddress('Tunisie')
                     ->setPostalCode('1145')
                     ->setCity('Tunis')
                     ->setCountry('France')
                     ->setIsActive(true)
                     ->setRoles(['ROLE_USER']);

                // Hacher le mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($user, 'salma123');
                $user->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                $io->success('✅ Utilisateur créé avec succès !');
            }
            
            // Sauvegarder les changements
            $this->entityManager->flush();
            
            $io->section('📊 Informations de connexion');
            $io->table(
                ['Champ', 'Valeur'],
                [
                    ['Email', 'ferjanisalma50@gmail.com'],
                    ['Mot de passe', 'salma123'],
                    ['Rôles', 'ROLE_USER'],
                    ['Statut', 'Actif']
                ]
            );
            
            $io->success('🎉 Utilisateur prêt pour la connexion !');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
