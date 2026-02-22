<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260217155657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, file VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, duration INT NOT NULL, status VARCHAR(255) NOT NULL, difficulty VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, course_id INT DEFAULT NULL, INDEX IDX_AC74095A591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, course_file VARCHAR(255) DEFAULT NULL, course_link VARCHAR(255) DEFAULT NULL, teacher_email VARCHAR(255) NOT NULL, semester VARCHAR(255) NOT NULL, difficulty_level VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, priority VARCHAR(255) NOT NULL, coefficient DOUBLE PRECISION NOT NULL, status VARCHAR(50) NOT NULL, duration INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_169E6FB9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, duration INT NOT NULL, location VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, priority VARCHAR(255) NOT NULL, difficulty INT NOT NULL, date DATE NOT NULL, motivation_id INT DEFAULT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3BAE0AA78EDBCD4E (motivation_id), INDEX IDX_3BAE0AA7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exam (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date DATETIME NOT NULL, duration INT NOT NULL, grade DOUBLE PRECISION DEFAULT NULL, difficulty VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, file VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, course_id INT DEFAULT NULL, INDEX IDX_38BBA6C6591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, capacity INT NOT NULL, group_photo VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, creator_id INT NOT NULL, INDEX IDX_6DC044C561220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE member_group (id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, UNIQUE INDEX UNIQ_FE1D136FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE motivation (id INT AUTO_INCREMENT NOT NULL, motivation_level INT NOT NULL, emotion VARCHAR(255) NOT NULL, preparation VARCHAR(255) NOT NULL, reward VARCHAR(255) NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_E06073EDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE objective (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, estimated_duration VARCHAR(255) NOT NULL, real_duration INT NOT NULL, priority VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, reason VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE pomodoro_session (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, duration INT NOT NULL, status VARCHAR(255) NOT NULL, started_at DATETIME DEFAULT NULL, ended_at DATETIME DEFAULT NULL, event_id INT NOT NULL, INDEX IDX_6FFF4BB271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, resource VARCHAR(255) NOT NULL, deadline DATE NOT NULL, type VARCHAR(255) NOT NULL, group_id INT DEFAULT NULL, INDEX IDX_2FB3D0EEFE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE project_task (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, project_id INT DEFAULT NULL, INDEX IDX_6BEF133D166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, repeat_count INT NOT NULL, status VARCHAR(255) NOT NULL, difficulty INT NOT NULL, impact DOUBLE PRECISION NOT NULL, objective_id INT DEFAULT NULL, INDEX IDX_527EDB2573484933 (objective_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, date_of_birth DATE DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, statut VARCHAR(255) DEFAULT NULL, profile_picture VARCHAR(255) DEFAULT NULL, education_level VARCHAR(255) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, skills JSON DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA78EDBCD4E FOREIGN KEY (motivation_id) REFERENCES motivation (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE exam ADD CONSTRAINT FK_38BBA6C6591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C561220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE member_group ADD CONSTRAINT FK_FE1D136FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE motivation ADD CONSTRAINT FK_E06073EDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE pomodoro_session ADD CONSTRAINT FK_6FFF4BB271F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT FK_6BEF133D166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2573484933 FOREIGN KEY (objective_id) REFERENCES objective (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A591CC992');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9A76ED395');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA78EDBCD4E');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE exam DROP FOREIGN KEY FK_38BBA6C6591CC992');
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C561220EA6');
        $this->addSql('ALTER TABLE member_group DROP FOREIGN KEY FK_FE1D136FE54D947');
        $this->addSql('ALTER TABLE motivation DROP FOREIGN KEY FK_E06073EDA76ED395');
        $this->addSql('ALTER TABLE pomodoro_session DROP FOREIGN KEY FK_6FFF4BB271F7E88B');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEFE54D947');
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY FK_6BEF133D166D1F9C');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2573484933');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE exam');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE member_group');
        $this->addSql('DROP TABLE motivation');
        $this->addSql('DROP TABLE objective');
        $this->addSql('DROP TABLE pomodoro_session');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE project_task');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
