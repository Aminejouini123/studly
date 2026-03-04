<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222161947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `group` CHANGE group_photo group_photo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project CHANGE resource resource VARCHAR(255) DEFAULT NULL, CHANGE deadline deadline DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `group` CHANGE group_photo group_photo VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE project CHANGE resource resource VARCHAR(255) NOT NULL, CHANGE deadline deadline DATE NOT NULL');
    }
}
