<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223122508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity ADD type VARCHAR(50) NOT NULL, ADD instructions LONGTEXT DEFAULT NULL, ADD expected_output LONGTEXT DEFAULT NULL, ADD hints LONGTEXT DEFAULT NULL, ADD completed_at DATETIME DEFAULT NULL, ADD assigned_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AC74095AADF66B1A ON activity (assigned_user_id)');
        $this->addSql('ALTER TABLE event ADD start_time DATETIME DEFAULT NULL, ADD end_time DATETIME DEFAULT NULL, ADD color VARCHAR(50) DEFAULT NULL, ADD category VARCHAR(100) DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD all_day TINYINT(1) DEFAULT NULL, ADD reminder_minutes INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `group` CHANGE group_photo group_photo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAD173940B');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('DROP INDEX IDX_BF5476CAD173940B ON notification');
        $this->addSql('ALTER TABLE notification DROP recommendation_id, DROP title, DROP type, CHANGE user_id user_id INT NOT NULL, CHANGE content content VARCHAR(255) NOT NULL, CHANGE is_read is_read TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEFE54D947');
        $this->addSql('ALTER TABLE project CHANGE resource resource VARCHAR(255) DEFAULT NULL, CHANGE deadline deadline DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_task ADD deadline DATETIME DEFAULT NULL, ADD completed_at DATETIME DEFAULT NULL, ADD assigned_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT FK_6BEF133DADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6BEF133DADF66B1A ON project_task (assigned_user_id)');
        $this->addSql('ALTER TABLE task ADD deadline DATETIME DEFAULT NULL, ADD completed_at DATETIME DEFAULT NULL, ADD assigned_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25ADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_527EDB25ADF66B1A ON task (assigned_user_id)');
        $this->addSql('DROP TABLE recommendation');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recommendation (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, url VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, source VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, image_url VARCHAR(500) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, match_score DOUBLE PRECISION NOT NULL, missing_skills JSON NOT NULL, matched_skills JSON NOT NULL, ai_summary LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, salary VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, location VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, is_read TINYINT(1) DEFAULT 0 NOT NULL, is_saved TINYINT(1) DEFAULT 0 NOT NULL, is_ignored TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E11CA906A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095AADF66B1A');
        $this->addSql('DROP INDEX IDX_AC74095AADF66B1A ON activity');
        $this->addSql('ALTER TABLE activity DROP type, DROP instructions, DROP expected_output, DROP hints, DROP completed_at, DROP assigned_user_id');
        $this->addSql('ALTER TABLE event DROP start_time, DROP end_time, DROP color, DROP category, DROP notes, DROP all_day, DROP reminder_minutes');
        $this->addSql('ALTER TABLE `group` CHANGE group_photo group_photo VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification ADD recommendation_id INT DEFAULT NULL, ADD title VARCHAR(255) NOT NULL, ADD type VARCHAR(50) NOT NULL, CHANGE content content LONGTEXT NOT NULL, CHANGE is_read is_read TINYINT(1) DEFAULT 0 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAD173940B FOREIGN KEY (recommendation_id) REFERENCES recommendation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BF5476CAD173940B ON notification (recommendation_id)');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEFE54D947');
        $this->addSql('ALTER TABLE project CHANGE resource resource VARCHAR(255) NOT NULL, CHANGE deadline deadline DATE NOT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY FK_6BEF133DADF66B1A');
        $this->addSql('DROP INDEX IDX_6BEF133DADF66B1A ON project_task');
        $this->addSql('ALTER TABLE project_task DROP deadline, DROP completed_at, DROP assigned_user_id');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25ADF66B1A');
        $this->addSql('DROP INDEX IDX_527EDB25ADF66B1A ON task');
        $this->addSql('ALTER TABLE task DROP deadline, DROP completed_at, DROP assigned_user_id');
    }
}
