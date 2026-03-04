<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304115750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY `FK_6DC044C561220EA6`');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C561220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY `FK_F11D61A2CD53EDB6`');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY `FK_F11D61A2F624B39D`');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2F624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_BF5476CAA76ED395`');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pomodoro_session DROP FOREIGN KEY `FK_6FFF4BB271F7E88B`');
        $this->addSql('ALTER TABLE pomodoro_session ADD CONSTRAINT FK_6FFF4BB271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C561220EA6');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT `FK_6DC044C561220EA6` FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2F624B39D');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2CD53EDB6');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT `FK_F11D61A2F624B39D` FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT `FK_F11D61A2CD53EDB6` FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_BF5476CAA76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE pomodoro_session DROP FOREIGN KEY FK_6FFF4BB271F7E88B');
        $this->addSql('ALTER TABLE pomodoro_session ADD CONSTRAINT `FK_6FFF4BB271F7E88B` FOREIGN KEY (event_id) REFERENCES event (id)');
    }
}
