<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403110000 extends AbstractMigration
{
    private function isMySql(): bool
    {
        return 'mysql' === $this->connection->getDatabasePlatform()->getName();
    }

    private function isPostgreSql(): bool
    {
        return 'postgresql' === $this->connection->getDatabasePlatform()->getName();
    }
    public function getDescription(): string
    {
        return 'Add end date to event table';
    }

    public function up(Schema $schema): void
    {
        if ($this->isMySql()) {
            $this->addSql('ALTER TABLE event ADD end_date DATETIME DEFAULT NULL');

            return;
        }

        if ($this->isPostgreSql()) {
            $this->addSql('ALTER TABLE event ADD end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

            return;
        }

        $this->abortIf(true, sprintf('Unsupported database platform: %s', $this->connection->getDatabasePlatform()->getName()));
    }

    public function down(Schema $schema): void
    {
        if (! $this->isMySql() && ! $this->isPostgreSql()) {
            $this->abortIf(true, sprintf('Unsupported database platform: %s', $this->connection->getDatabasePlatform()->getName()));
        }
        $this->addSql('ALTER TABLE event DROP end_date');
    }
}
