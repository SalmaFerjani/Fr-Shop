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
    name: 'app:list-users',
    description: 'Liste tous les utilisateurs de la base de données',
)]
class ListUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('👥 Liste des utilisateurs');

        try {
            $users = $this->entityManager->getRepository(User::class)->findAll();
            
            if (empty($users)) {
                $io->warning('Aucun utilisateur trouvé dans la base de données.');
                return Command::SUCCESS;
            }
            
            $io->text(sprintf('Nombre d\'utilisateurs trouvés : %d', count($users)));
            
            $tableData = [];
            foreach ($users as $user) {
                $roles = $user->getRoles();
                $tableData[] = [
                    $user->getId(),
                    $user->getEmail(),
                    $user->getFirstName() . ' ' . $user->getLastName(),
                    implode(', ', $roles),
                    $user->isIsActive() ? 'Actif' : 'Inactif',
                    $user->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }
            
            $io->table(
                ['ID', 'Email', 'Nom complet', 'Rôles', 'Statut', 'Créé le'],
                $tableData
            );
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
