<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024090415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_date ON tasks');
        $this->addSql('DROP INDEX idx_status ON tasks');
        $this->addSql('ALTER TABLE tasks ADD start_time TIME DEFAULT NULL, ADD end_time TIME DEFAULT NULL, CHANGE status status VARCHAR(20) NOT NULL, CHANGE duration_seconds duration_seconds INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tasks DROP start_time, DROP end_time, CHANGE status status VARCHAR(20) DEFAULT \'pending\' NOT NULL, CHANGE duration_seconds duration_seconds INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX idx_date ON tasks (date)');
        $this->addSql('CREATE INDEX idx_status ON tasks (status)');
    }
}
