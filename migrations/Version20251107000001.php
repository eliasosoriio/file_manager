<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Añade campo is_regac a la tabla tasks
 */
final class Version20251107000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_regac field to tasks table';
    }

    public function up(Schema $schema): void
    {
        // Agregar columna is_regac a la tabla tasks
        $this->addSql('ALTER TABLE tasks ADD is_regac TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Eliminar columna is_regac de la tabla tasks
        $this->addSql('ALTER TABLE tasks DROP is_regac');
    }
}
