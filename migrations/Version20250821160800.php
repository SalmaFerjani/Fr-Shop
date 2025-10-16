<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250821160800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix user roles data type issue - convert integer roles to proper JSON array format';
    }

    public function up(Schema $schema): void
    {
        // Convert integer roles to proper JSON array format
        $this->addSql("UPDATE `user` SET roles = JSON_ARRAY('ROLE_USER') WHERE roles = '0' OR roles = 0");
        $this->addSql("UPDATE `user` SET roles = JSON_ARRAY('ROLE_ADMIN') WHERE roles = '1' OR roles = 1");
        $this->addSql("UPDATE `user` SET roles = JSON_ARRAY('ROLE_USER') WHERE roles IS NULL OR roles = ''");
    }

    public function down(Schema $schema): void
    {
        // This migration is not reversible as it fixes data corruption
        $this->addSql("-- This migration fixes data corruption and cannot be reversed");
    }
}
