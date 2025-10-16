<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur administrateur',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Email de l\'administrateur', 'admin@boutiquefrancaise.fr')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Mot de passe de l\'administrateur', 'admin123')
            ->addOption('first-name', null, InputOption::VALUE_OPTIONAL, 'Prénom de l\'administrateur', 'Admin')
            ->addOption('last-name', null, InputOption::VALUE_OPTIONAL, 'Nom de l\'administrateur', 'Boutique')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('👑 Création d\'un utilisateur administrateur');

        $email = $input->getOption('email');
        $password = $input->getOption('password');
        $firstName = $input->getOption('first-name');
        $lastName = $input->getOption('last-name');

        try {
            // Vérifier si l'utilisateur existe déjà
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            
            if ($existingUser) {
                $io->text('✅ Utilisateur existant trouvé : ' . $existingUser->getEmail());
                
                // Mettre à jour les rôles et le mot de passe
                $existingUser->setRoles(['ROLE_ADMIN']);
                $existingUser->setPassword($this->passwordHasher->hashPassword($existingUser, $password));
                $existingUser->setFirstName($firstName);
                $existingUser->setLastName($lastName);
                $existingUser->setIsActive(true);
                
                $this->entityManager->persist($existingUser);
                $io->success('✅ Utilisateur existant mis à jour avec les privilèges administrateur !');
                
            } else {
                $io->text('❌ Utilisateur non trouvé, création...');
                
                // Créer un nouvel utilisateur administrateur
                $user = new User();
                $user->setEmail($email)
                     ->setFirstName($firstName)
                     ->setLastName($lastName)
                     ->setPhone('+33 1 23 45 67 89')
                     ->setAddress('123 Rue de la Mode')
                     ->setPostalCode('75001')
                     ->setCity('Paris')
                     ->setCountry('France')
                     ->setIsActive(true)
                     ->setRoles(['ROLE_ADMIN']);

                // Hacher le mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                $io->success('✅ Utilisateur administrateur créé avec succès !');
            }
            
            // Sauvegarder les changements
            $this->entityManager->flush();
            
            $io->section('👑 Informations de connexion administrateur');
            $io->table(
                ['Champ', 'Valeur'],
                [
                    ['Email', $email],
                    ['Mot de passe', $password],
                    ['Rôles', 'ROLE_ADMIN'],
                    ['Nom complet', $firstName . ' ' . $lastName],
                    ['Statut', 'Actif']
                ]
            );
            
            $io->section('🔗 Accès administrateur');
            $io->text('Vous pouvez maintenant accéder à l\'interface d\'administration :');
            $io->text('• URL : /admin');
            $io->text('• Email : ' . $email);
            $io->text('• Mot de passe : ' . $password);
            
            $io->success('🎉 Compte administrateur prêt !');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
