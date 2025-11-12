<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Cambia el valor por defecto de is_regac a true
 */
final class Version20251107000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change default value of is_regac to true';
    }

    public function up(Schema $schema): void
    {
        // Cambiar el valor por defecto de is_regac a 1 (true)
        $this->addSql('ALTER TABLE tasks MODIFY is_regac TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Restaurar el valor por defecto a 0 (false)
        $this->addSql('ALTER TABLE tasks MODIFY is_regac TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
