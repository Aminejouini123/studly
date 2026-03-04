<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304114409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY `FK_AC74095A591CC992`');
        $this->addSql('ALTER TABLE activity CHANGE course_id course_id INT NOT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exam DROP FOREIGN KEY `FK_38BBA6C6591CC992`');
        $this->addSql('ALTER TABLE exam CHANGE course_id course_id INT NOT NULL');
        $this->addSql('ALTER TABLE exam ADD CONSTRAINT FK_38BBA6C6591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY `FK_F11D61A2FE54D947`');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_B6BD307FFE54D947`');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project CHANGE group_id group_id INT NOT NULL');
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY `FK_6BEF133D166D1F9C`');
        $this->addSql('ALTER TABLE project_task ADD deliverable VARCHAR(255) DEFAULT NULL, ADD grade INT DEFAULT NULL, ADD attachment VARCHAR(255) DEFAULT NULL, CHANGE project_id project_id INT NOT NULL');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT FK_6BEF133D166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A591CC992');
        $this->addSql('ALTER TABLE activity CHANGE course_id course_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT `FK_AC74095A591CC992` FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE exam DROP FOREIGN KEY FK_38BBA6C6591CC992');
        $this->addSql('ALTER TABLE exam CHANGE course_id course_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE exam ADD CONSTRAINT `FK_38BBA6C6591CC992` FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2FE54D947');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT `FK_F11D61A2FE54D947` FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FFE54D947');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_B6BD307FFE54D947` FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE project CHANGE group_id group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_task DROP FOREIGN KEY FK_6BEF133D166D1F9C');
        $this->addSql('ALTER TABLE project_task DROP deliverable, DROP grade, DROP attachment, CHANGE project_id project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_task ADD CONSTRAINT `FK_6BEF133D166D1F9C` FOREIGN KEY (project_id) REFERENCES project (id)');
    }
}
