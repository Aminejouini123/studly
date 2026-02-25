<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225013145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE group_user (group_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_A4C98D39FE54D947 (group_id), INDEX IDX_A4C98D39A76ED395 (user_id), PRIMARY KEY(group_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE invitation (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_F11D61A2F624B39D (sender_id), INDEX IDX_F11D61A2CD53EDB6 (receiver_id), INDEX IDX_F11D61A2FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307FFE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(255) NOT NULL, link VARCHAR(255) DEFAULT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT FK_A4C98D39FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT FK_A4C98D39A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE member_group DROP FOREIGN KEY FK_FE1D136FE54D947');
        $this->addSql('DROP TABLE member_group');
        $this->addSql('ALTER TABLE activity ADD completed_at DATETIME DEFAULT NULL, ADD assigned_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AC74095AADF66B1A ON activity (assigned_user_id)');
        $this->addSql('ALTER TABLE `group` CHANGE group_photo group_photo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEFE54D947');
        $this->addSql('ALTER TABLE project CHANGE resource resource VARCHAR(255) DEFAULT NULL, CHANGE deadline deadline DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_task ADD deadline DATETIME DEFAULT NULL, ADD completed_at DATETIME DEFAULT NULL, ADD assigned_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT FK_6BEF133DADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6BEF133DADF66B1A ON project_task (assigned_user_id)');
        $this->addSql('ALTER TABLE task ADD deadline DATETIME DEFAULT NULL, ADD completed_at DATETIME DEFAULT NULL, ADD assigned_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25ADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_527EDB25ADF66B1A ON task (assigned_user_id)');
        $this->addSql('ALTER TABLE user ADD score INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE member_group (id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, UNIQUE INDEX UNIQ_FE1D136FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE member_group ADD CONSTRAINT FK_FE1D136FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE group_user DROP FOREIGN KEY FK_A4C98D39FE54D947');
        $this->addSql('ALTER TABLE group_user DROP FOREIGN KEY FK_A4C98D39A76ED395');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2F624B39D');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2CD53EDB6');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2FE54D947');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FFE54D947');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('DROP TABLE group_user');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095AADF66B1A');
        $this->addSql('DROP INDEX IDX_AC74095AADF66B1A ON activity');
        $this->addSql('ALTER TABLE activity DROP completed_at, DROP assigned_user_id');
        $this->addSql('ALTER TABLE `group` CHANGE group_photo group_photo VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEFE54D947');
        $this->addSql('ALTER TABLE project CHANGE resource resource VARCHAR(255) NOT NULL, CHANGE deadline deadline DATE NOT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY FK_6BEF133DADF66B1A');
        $this->addSql('DROP INDEX IDX_6BEF133DADF66B1A ON project_task');
        $this->addSql('ALTER TABLE project_task DROP deadline, DROP completed_at, DROP assigned_user_id');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25ADF66B1A');
        $this->addSql('DROP INDEX IDX_527EDB25ADF66B1A ON task');
        $this->addSql('ALTER TABLE task DROP deadline, DROP completed_at, DROP assigned_user_id');
        $this->addSql('ALTER TABLE user DROP score');
    }
}
