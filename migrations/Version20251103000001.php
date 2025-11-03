<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Refactoriza el sistema de tareas:
 * - Crea tabla projects
 * - Crea tabla time_entries (para registro de horas)
 * - Modifica tabla tasks (simplifica para gestión de tareas)
 */
final class Version20251103000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactor tasks system: create projects, time_entries, and simplify tasks table';
    }

    public function up(Schema $schema): void
    {
        // Crear tabla de proyectos
        $this->addSql('CREATE TABLE projects (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            color VARCHAR(50) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Crear tabla de registros de tiempo
        $this->addSql('CREATE TABLE time_entries (
            id INT AUTO_INCREMENT NOT NULL,
            task_id INT NOT NULL,
            description LONGTEXT DEFAULT NULL,
            duration_seconds INT NOT NULL,
            start_time TIME DEFAULT NULL,
            end_time TIME DEFAULT NULL,
            date DATE NOT NULL,
            started_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_960AC3F18DB60186 (task_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Renombrar la tabla tasks actual a tasks_backup temporalmente
        $this->addSql('RENAME TABLE tasks TO tasks_backup');

        // Crear nueva tabla tasks con la estructura simplificada
        $this->addSql('CREATE TABLE tasks (
            id INT AUTO_INCREMENT NOT NULL,
            project_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT \'pending\',
            ticket_number VARCHAR(100) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_50586597166D1F9C (project_id),
            INDEX IDX_505865977B00651C (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Agregar foreign keys
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id)');
        $this->addSql('ALTER TABLE time_entries ADD CONSTRAINT FK_960AC3F18DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id)');
    }

    public function down(Schema $schema): void
    {
        // Eliminar foreign keys
        $this->addSql('ALTER TABLE time_entries DROP FOREIGN KEY FK_960AC3F18DB60186');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597166D1F9C');

        // Eliminar las nuevas tablas
        $this->addSql('DROP TABLE time_entries');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('DROP TABLE projects');

        // Restaurar la tabla tasks original
        $this->addSql('RENAME TABLE tasks_backup TO tasks');
    }
}
