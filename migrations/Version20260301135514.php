<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301135514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_action (id INT AUTO_INCREMENT NOT NULL, action_type VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, entity_id INT DEFAULT NULL, entity_type VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_229E97AFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_action ADD CONSTRAINT FK_229E97AFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE face_embeddings');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE face_embeddings (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, descriptor JSON NOT NULL, UNIQUE INDEX ix_face_embeddings_user_id (user_id), INDEX ix_face_embeddings_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_action DROP FOREIGN KEY FK_229E97AFA76ED395');
        $this->addSql('DROP TABLE user_action');
    }
}
