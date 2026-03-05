<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305012104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity CHANGE course_id course_id INT NOT NULL');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY `FK_3BAE0AA78EDBCD4E`');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA78EDBCD4E FOREIGN KEY (motivation_id) REFERENCES motivation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exam DROP FOREIGN KEY `FK_38BBA6C6591CC992`');
        $this->addSql('ALTER TABLE exam CHANGE course_id course_id INT NOT NULL');
        $this->addSql('ALTER TABLE exam ADD CONSTRAINT FK_38BBA6C6591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groups DROP FOREIGN KEY `FK_F06D397061220EA6`');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_F06D397061220EA6 FOREIGN KEY (creator_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE motivation DROP FOREIGN KEY `FK_E06073EDA76ED395`');
        $this->addSql('ALTER TABLE motivation CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE motivation ADD CONSTRAINT FK_E06073EDA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY `FK_527EDB2573484933`');
        $this->addSql('ALTER TABLE task CHANGE objective_id objective_id INT NOT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2573484933 FOREIGN KEY (objective_id) REFERENCES objective (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity CHANGE course_id course_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA78EDBCD4E');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT `FK_3BAE0AA78EDBCD4E` FOREIGN KEY (motivation_id) REFERENCES motivation (id)');
        $this->addSql('ALTER TABLE exam DROP FOREIGN KEY FK_38BBA6C6591CC992');
        $this->addSql('ALTER TABLE exam CHANGE course_id course_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE exam ADD CONSTRAINT `FK_38BBA6C6591CC992` FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE groups DROP FOREIGN KEY FK_F06D397061220EA6');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT `FK_F06D397061220EA6` FOREIGN KEY (creator_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE motivation DROP FOREIGN KEY FK_E06073EDA76ED395');
        $this->addSql('ALTER TABLE motivation CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE motivation ADD CONSTRAINT `FK_E06073EDA76ED395` FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2573484933');
        $this->addSql('ALTER TABLE task CHANGE objective_id objective_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT `FK_527EDB2573484933` FOREIGN KEY (objective_id) REFERENCES objective (id)');
    }
}
