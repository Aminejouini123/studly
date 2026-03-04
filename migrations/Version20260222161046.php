<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222161046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE password_reset_token DROP FOREIGN KEY FK_6B7BA4B6A76ED395');
        $this->addSql('DROP TABLE password_reset_token');
        $this->addSql('ALTER TABLE activity DROP type, DROP instructions, DROP expected_output, DROP hints');
        $this->addSql('ALTER TABLE event DROP start_time, DROP end_time, DROP color, DROP category, DROP notes, DROP all_day, DROP reminder_minutes');
        $this->addSql('ALTER TABLE project_task DROP submission_file');
        $this->addSql('ALTER TABLE user DROP google_id, DROP is_verified, DROP verification_code');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE password_reset_token (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_6B7BA4B6A76ED395 (user_id), UNIQUE INDEX UNIQ_6B7BA4B65F37A13B (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_6B7BA4B6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity ADD type VARCHAR(50) NOT NULL, ADD instructions LONGTEXT DEFAULT NULL, ADD expected_output LONGTEXT DEFAULT NULL, ADD hints LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD start_time DATETIME DEFAULT NULL, ADD end_time DATETIME DEFAULT NULL, ADD color VARCHAR(50) DEFAULT NULL, ADD category VARCHAR(100) DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD all_day TINYINT(1) DEFAULT NULL, ADD reminder_minutes INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_task ADD submission_file VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD google_id VARCHAR(255) DEFAULT NULL, ADD is_verified TINYINT(1) NOT NULL, ADD verification_code VARCHAR(6) DEFAULT NULL');
    }
}
