<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226091252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('DROP TABLE face_embeddings');
        $this->addSql('ALTER TABLE event ADD google_event_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2FE54D947');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FFE54D947');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pomodoro_session ADD focus_score DOUBLE PRECISION DEFAULT NULL, ADD focus_logs JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY FK_6BEF133D166D1F9C');
        $this->addSql('ALTER TABLE project_task ADD deliverable VARCHAR(255) DEFAULT NULL, ADD grade INT DEFAULT NULL, ADD attachment VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT FK_6BEF133D166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD google_access_token LONGTEXT DEFAULT NULL, ADD google_refresh_token LONGTEXT DEFAULT NULL, ADD google_token_expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE TABLE face_embeddings (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, descriptor JSON NOT NULL, UNIQUE INDEX ix_face_embeddings_user_id (user_id), INDEX ix_face_embeddings_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE event DROP google_event_id');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2FE54D947');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FFE54D947');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE pomodoro_session DROP focus_score, DROP focus_logs');
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY FK_6BEF133D166D1F9C');
        $this->addSql('ALTER TABLE project_task DROP deliverable, DROP grade, DROP attachment');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT FK_6BEF133D166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE user DROP google_access_token, DROP google_refresh_token, DROP google_token_expires_at');
    }
}
