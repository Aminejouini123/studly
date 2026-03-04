<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225024044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD google_event_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pomodoro_session ADD focus_score DOUBLE PRECISION DEFAULT NULL, ADD focus_logs JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD google_access_token LONGTEXT DEFAULT NULL, ADD google_refresh_token LONGTEXT DEFAULT NULL, ADD google_token_expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP google_event_id');
        $this->addSql('ALTER TABLE pomodoro_session DROP focus_score, DROP focus_logs');
        $this->addSql('ALTER TABLE user DROP google_access_token, DROP google_refresh_token, DROP google_token_expires_at');
    }
}
