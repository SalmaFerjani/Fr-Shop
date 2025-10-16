<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fix-user-roles',
    description: 'Corrige les rôles des utilisateurs dans la base de données',
)]
class FixUserRolesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔧 Correction des rôles utilisateurs');

        try {
            // Récupérer tous les utilisateurs
            $users = $this->entityManager->getRepository(User::class)->findAll();
            
            $fixedCount = 0;
            
            foreach ($users as $user) {
                $roles = $user->getRoles();
                
                // Vérifier si les rôles sont dans un format incorrect
                if (empty($roles) || !is_array($roles) || in_array('ROLE_USER', $roles) === false) {
                    // Corriger les rôles
                    $user->setRoles(['ROLE_USER']);
                    $this->entityManager->persist($user);
                    $fixedCount++;
                    
                    $io->text(sprintf('✅ Utilisateur %s corrigé', $user->getEmail()));
                }
            }
            
            // Sauvegarder les changements
            $this->entityManager->flush();
            
            $io->success(sprintf('🎉 %d utilisateurs corrigés avec succès !', $fixedCount));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors de la correction : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
