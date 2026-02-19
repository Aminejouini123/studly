<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Add creator_id and created_at to group table
 */
final class Version20260209120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add creator_id and created_at fields to group table for Group management';
    }

    public function up(Schema $schema): void
    {
        // Only add columns if the table exists
        if (!$schema->hasTable('group')) {
            return;
        }
        
        $table = $schema->getTable('group');
        
        if (!$table->hasColumn('created_at')) {
            $this->addSql('ALTER TABLE `group` ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'(DC2Type:datetime_immutable)\'');
        }
        
        if (!$table->hasColumn('creator_id')) {
            $this->addSql('ALTER TABLE `group` ADD creator_id INT NOT NULL');
            $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C561220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
            $this->addSql('CREATE INDEX IDX_6DC044C561220EA6 ON `group` (creator_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // Remove the foreign key and columns
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C561220EA6');
        $this->addSql('DROP INDEX IDX_6DC044C561220EA6 ON `group`');
        $this->addSql('ALTER TABLE `group` DROP creator_id');
        $this->addSql('ALTER TABLE `group` DROP created_at');
    }
}
