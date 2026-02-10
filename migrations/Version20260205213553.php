<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260205213553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Skip if tables already exist (for database that were set up manually)
        if (!$schema->hasTable('event')) {
            $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, duree INT NOT NULL, lieu VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, priorite VARCHAR(255) NOT NULL, difficulte INT NOT NULL, date DATE NOT NULL, motivation_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3BAE0AA78EDBCD4E (motivation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        }
        if (!$schema->hasTable('motivation')) {
            $this->addSql('CREATE TABLE motivation (id INT AUTO_INCREMENT NOT NULL, niveau_motivation INT NOT NULL, emotion VARCHAR(255) NOT NULL, preparation VARCHAR(255) NOT NULL, recompense VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        }
        if (!$schema->hasTable('user')) {
            $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, prénom VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, niveau_education VARCHAR(255) NOT NULL, specialite VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        }
        if (!$schema->hasTable('messenger_messages')) {
            $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        }
        
        // Add foreign key if tables exist but relationship doesn't
        if ($schema->hasTable('event') && $schema->getTable('event')->hasForeignKey('FK_3BAE0AA78EDBCD4E') === false) {
            $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA78EDBCD4E FOREIGN KEY (motivation_id) REFERENCES motivation (id)');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA78EDBCD4E');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE motivation');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
