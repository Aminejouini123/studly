<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219123941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY `FK_AC74095AE872BCBF`');
        $this->addSql('DROP INDEX idx_ac74095ae872bcbf ON activity');
        $this->addSql('CREATE INDEX IDX_AC74095AADF66B1A ON activity (assigned_user_id)');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT `FK_AC74095AE872BCBF` FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_169E6FB9A76ED395 ON course (user_id)');
        $this->addSql('ALTER TABLE exam CHANGE file file VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `group` CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY `FK_8A1B0C2BE872BCBF`');
        $this->addSql('DROP INDEX idx_8a1b0c2be872bcbf ON project_task');
        $this->addSql('CREATE INDEX IDX_6BEF133DADF66B1A ON project_task (assigned_user_id)');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT `FK_8A1B0C2BE872BCBF` FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY `FK_527EDB25E872BCBF`');
        $this->addSql('DROP INDEX idx_527edb25e872bcbf ON task');
        $this->addSql('CREATE INDEX IDX_527EDB25ADF66B1A ON task (assigned_user_id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT `FK_527EDB25E872BCBF` FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095AADF66B1A');
        $this->addSql('DROP INDEX idx_ac74095aadf66b1a ON activity');
        $this->addSql('CREATE INDEX IDX_AC74095AE872BCBF ON activity (assigned_user_id)');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9A76ED395');
        $this->addSql('DROP INDEX IDX_169E6FB9A76ED395 ON course');
        $this->addSql('ALTER TABLE course DROP user_id');
        $this->addSql('ALTER TABLE exam CHANGE file file VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `group` CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY FK_6BEF133DADF66B1A');
        $this->addSql('DROP INDEX idx_6bef133dadf66b1a ON project_task');
        $this->addSql('CREATE INDEX IDX_8A1B0C2BE872BCBF ON project_task (assigned_user_id)');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT FK_6BEF133DADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25ADF66B1A');
        $this->addSql('DROP INDEX idx_527edb25adf66b1a ON task');
        $this->addSql('CREATE INDEX IDX_527EDB25E872BCBF ON task (assigned_user_id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25ADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
    }
}
