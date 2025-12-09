<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add 'on_hold' status to tasks table
 * Note: No database schema change needed as status column is already VARCHAR(50)
 * This migration documents the addition of the 'on_hold' status value
 */
final class Version20251209000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add on_hold status to tasks (documentation only, column already supports it)';
    }

    public function up(Schema $schema): void
    {
        // No schema changes needed - status column is already VARCHAR(50)
        // This migration documents that 'on_hold' is now a valid status value
        // Valid statuses: pending, in_progress, recurring, on_hold, completed
    }

    public function down(Schema $schema): void
    {
        // Update any on_hold tasks to pending before removing the status
        $this->addSql("UPDATE tasks SET status = 'pending' WHERE status = 'on_hold'");
    }
}
