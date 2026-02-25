<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Final schema alignment with entities
 */
final class Version20260211120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Final schema alignment with entity definitions';
    }

    public function up(Schema $schema): void
    {
        // Schema should be aligned at this point
        // This migration exists for future schema changes
    }

    public function down(Schema $schema): void
    {
        // Downgrade not supported
    }
}
