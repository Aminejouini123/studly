<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260216000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add gamification score system';
    }

    public function up(Schema $schema): void
    {
        // Add score field to user table
        $this->addSql('ALTER TABLE user ADD score INT NOT NULL DEFAULT 0');

        // Add assigned_user_id and deadline to task table
        $this->addSql('ALTER TABLE task ADD assigned_user_id INT DEFAULT NULL, ADD deadline DATETIME DEFAULT NULL, ADD completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25E872BCBF FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_527EDB25E872BCBF ON task (assigned_user_id)');

        // Add assigned_user_id and deadline to project_task table
        $this->addSql('ALTER TABLE project_task ADD assigned_user_id INT DEFAULT NULL, ADD deadline DATETIME DEFAULT NULL, ADD completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT FK_8A1B0C2BE872BCBF FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8A1B0C2BE872BCBF ON project_task (assigned_user_id)');

        // Add assigned_user_id and completed_at to activity table
        $this->addSql('ALTER TABLE activity ADD assigned_user_id INT DEFAULT NULL, ADD completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AE872BCBF FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AC74095AE872BCBF ON activity (assigned_user_id)');
    }

    public function down(Schema $schema): void
    {
        // Remove from activity table
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095AE872BCBF');
        $this->addSql('DROP INDEX IDX_AC74095AE872BCBF ON activity');
        $this->addSql('ALTER TABLE activity DROP assigned_user_id, DROP completed_at');

        // Remove from project_task table
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY FK_8A1B0C2BE872BCBF');
        $this->addSql('DROP INDEX IDX_8A1B0C2BE872BCBF ON project_task');
        $this->addSql('ALTER TABLE project_task DROP assigned_user_id, DROP deadline, DROP completed_at');

        // Remove from task table
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25E872BCBF');
        $this->addSql('DROP INDEX IDX_527EDB25E872BCBF ON task');
        $this->addSql('ALTER TABLE task DROP assigned_user_id, DROP deadline, DROP completed_at');

        // Remove score from user table
        $this->addSql('ALTER TABLE user DROP score');
    }
}