<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112171625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tasks_backup');
        $this->addSql('DROP INDEX IDX_505865977B00651C ON tasks');
        $this->addSql('ALTER TABLE tasks CHANGE status status VARCHAR(50) NOT NULL, CHANGE is_regac is_regac TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE time_entries RENAME INDEX idx_960ac3f18db60186 TO IDX_797F12A38DB60186');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tasks_backup (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, status VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, duration_seconds INT NOT NULL, date DATE NOT NULL, started_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, start_time TIME DEFAULT NULL, end_time TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE time_entries RENAME INDEX idx_797f12a38db60186 TO IDX_960AC3F18DB60186');
        $this->addSql('ALTER TABLE tasks CHANGE status status VARCHAR(50) DEFAULT \'pending\' NOT NULL, CHANGE is_regac is_regac TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('CREATE INDEX IDX_505865977B00651C ON tasks (status)');
    }
}
