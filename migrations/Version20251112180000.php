<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drop is_regac column from tasks table as it has been moved to time_entries
 */
final class Version20251112180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop is_regac column from tasks table';
    }

    public function up(Schema $schema): void
    {
        // Drop is_regac column from tasks table
        $this->addSql('ALTER TABLE tasks DROP COLUMN is_regac');
    }

    public function down(Schema $schema): void
    {
        // Restore is_regac column to tasks table
        $this->addSql('ALTER TABLE tasks ADD COLUMN is_regac TINYINT(1) NOT NULL DEFAULT 0');
    }
}
