<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305012929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, capacity INT NOT NULL, group_photo VARCHAR(255) DEFAULT NULL, category VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, creator_id INT NOT NULL, INDEX IDX_6DC044C561220EA6 (creator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, google_id VARCHAR(255) DEFAULT NULL, is_verified TINYINT NOT NULL, verification_code VARCHAR(6) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, date_of_birth DATE DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, statut VARCHAR(255) DEFAULT NULL, profile_picture VARCHAR(255) DEFAULT NULL, education_level VARCHAR(255) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, skills JSON DEFAULT NULL, score INT DEFAULT 0 NOT NULL, google_access_token LONGTEXT DEFAULT NULL, google_refresh_token LONGTEXT DEFAULT NULL, google_token_expires_at DATETIME DEFAULT NULL, email VARCHAR(180) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C561220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groups DROP FOREIGN KEY `FK_F06D397061220EA6`');
        $this->addSql('DROP TABLE groups');
        $this->addSql('DROP TABLE users');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY `FK_AC74095AADF66B1A`');
        $this->addSql('ALTER TABLE activity ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY `FK_169E6FB9A76ED395`');
        $this->addSql('ALTER TABLE course ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY `FK_3BAE0AA7A76ED395`');
        $this->addSql('ALTER TABLE event ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE exam ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE group_user DROP FOREIGN KEY `FK_A4C98D39A76ED395`');
        $this->addSql('ALTER TABLE group_user DROP FOREIGN KEY `FK_A4C98D39FE54D947`');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT FK_A4C98D39A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT FK_A4C98D39FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY `FK_F11D61A2CD53EDB6`');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY `FK_F11D61A2F624B39D`');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY `FK_F11D61A2FE54D947`');
        $this->addSql('ALTER TABLE invitation ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2F624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_B6BD307FF624B39D`');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_B6BD307FFE54D947`');
        $this->addSql('ALTER TABLE message ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE motivation DROP FOREIGN KEY `FK_E06073EDA76ED395`');
        $this->addSql('ALTER TABLE motivation ADD CONSTRAINT FK_E06073EDA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_BF5476CAA76ED395`');
        $this->addSql('ALTER TABLE notification ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE objective ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE password_reset_token DROP FOREIGN KEY `FK_6B7BA4B6A76ED395`');
        $this->addSql('ALTER TABLE password_reset_token ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_6B7BA4B6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pomodoro_session DROP FOREIGN KEY `FK_6FFF4BB271F7E88B`');
        $this->addSql('ALTER TABLE pomodoro_session ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE pomodoro_session ADD CONSTRAINT FK_6FFF4BB271F7E88B FOREIGN KEY (event_id) REFERENCES `event` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY `FK_2FB3D0EEFE54D947`');
        $this->addSql('ALTER TABLE project ADD updated_at DATETIME DEFAULT NULL, CHANGE group_id group_id INT NOT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY `FK_6BEF133DADF66B1A`');
        $this->addSql('ALTER TABLE project_task ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT FK_6BEF133DADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY `FK_527EDB25ADF66B1A`');
        $this->addSql('ALTER TABLE task ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25ADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groups (id INT AUTO_INCREMENT NOT NULL, capacity INT NOT NULL, group_photo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, category VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, creator_id INT NOT NULL, INDEX IDX_F06D397061220EA6 (creator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, google_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, is_verified TINYINT NOT NULL, verification_code VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, roles JSON NOT NULL, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_of_birth DATE DEFAULT NULL, phone_number VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, statut VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, profile_picture VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, education_level VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, job_title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, website VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, bio LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, skills JSON DEFAULT NULL, score INT DEFAULT 0 NOT NULL, google_access_token LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, google_refresh_token LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, google_token_expires_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT `FK_F06D397061220EA6` FOREIGN KEY (creator_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C561220EA6');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095AADF66B1A');
        $this->addSql('ALTER TABLE activity DROP updated_at');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT `FK_AC74095AADF66B1A` FOREIGN KEY (assigned_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9A76ED395');
        $this->addSql('ALTER TABLE course DROP updated_at');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT `FK_169E6FB9A76ED395` FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE `event` DROP FOREIGN KEY FK_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE `event` DROP updated_at');
        $this->addSql('ALTER TABLE `event` ADD CONSTRAINT `FK_3BAE0AA7A76ED395` FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE exam DROP updated_at');
        $this->addSql('ALTER TABLE group_user DROP FOREIGN KEY FK_A4C98D39FE54D947');
        $this->addSql('ALTER TABLE group_user DROP FOREIGN KEY FK_A4C98D39A76ED395');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT `FK_A4C98D39FE54D947` FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT `FK_A4C98D39A76ED395` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `invitation` DROP FOREIGN KEY FK_F11D61A2F624B39D');
        $this->addSql('ALTER TABLE `invitation` DROP FOREIGN KEY FK_F11D61A2CD53EDB6');
        $this->addSql('ALTER TABLE `invitation` DROP FOREIGN KEY FK_F11D61A2FE54D947');
        $this->addSql('ALTER TABLE `invitation` DROP updated_at');
        $this->addSql('ALTER TABLE `invitation` ADD CONSTRAINT `FK_F11D61A2F624B39D` FOREIGN KEY (sender_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE `invitation` ADD CONSTRAINT `FK_F11D61A2CD53EDB6` FOREIGN KEY (receiver_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE `invitation` ADD CONSTRAINT `FK_F11D61A2FE54D947` FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `message` DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE `message` DROP FOREIGN KEY FK_B6BD307FFE54D947');
        $this->addSql('ALTER TABLE `message` DROP updated_at');
        $this->addSql('ALTER TABLE `message` ADD CONSTRAINT `FK_B6BD307FF624B39D` FOREIGN KEY (sender_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE `message` ADD CONSTRAINT `FK_B6BD307FFE54D947` FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE motivation DROP FOREIGN KEY FK_E06073EDA76ED395');
        $this->addSql('ALTER TABLE motivation ADD CONSTRAINT `FK_E06073EDA76ED395` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `notification` DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE `notification` DROP updated_at');
        $this->addSql('ALTER TABLE `notification` ADD CONSTRAINT `FK_BF5476CAA76ED395` FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE objective DROP updated_at');
        $this->addSql('ALTER TABLE `password_reset_token` DROP FOREIGN KEY FK_6B7BA4B6A76ED395');
        $this->addSql('ALTER TABLE `password_reset_token` DROP updated_at');
        $this->addSql('ALTER TABLE `password_reset_token` ADD CONSTRAINT `FK_6B7BA4B6A76ED395` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pomodoro_session DROP FOREIGN KEY FK_6FFF4BB271F7E88B');
        $this->addSql('ALTER TABLE pomodoro_session DROP updated_at');
        $this->addSql('ALTER TABLE pomodoro_session ADD CONSTRAINT `FK_6FFF4BB271F7E88B` FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE `project` DROP FOREIGN KEY FK_2FB3D0EEFE54D947');
        $this->addSql('ALTER TABLE `project` DROP updated_at, CHANGE group_id group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `project` ADD CONSTRAINT `FK_2FB3D0EEFE54D947` FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `project_task` DROP FOREIGN KEY FK_6BEF133DADF66B1A');
        $this->addSql('ALTER TABLE `project_task` DROP updated_at');
        $this->addSql('ALTER TABLE `project_task` ADD CONSTRAINT `FK_6BEF133DADF66B1A` FOREIGN KEY (assigned_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE `task` DROP FOREIGN KEY FK_527EDB25ADF66B1A');
        $this->addSql('ALTER TABLE `task` DROP updated_at');
        $this->addSql('ALTER TABLE `task` ADD CONSTRAINT `FK_527EDB25ADF66B1A` FOREIGN KEY (assigned_user_id) REFERENCES users (id)');
    }
}
