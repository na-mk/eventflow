<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403130000 extends AbstractMigration
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
        return 'Add status to contact_message table';
    }

    public function up(Schema $schema): void
    {
        if ($this->isMySql()) {
            $this->addSql("ALTER TABLE contact_message ADD status VARCHAR(20) NOT NULL DEFAULT 'new'");

            return;
        }

        if ($this->isPostgreSql()) {
            $this->addSql("ALTER TABLE contact_message ADD status VARCHAR(20) DEFAULT 'new' NOT NULL");

            return;
        }

        $this->abortIf(true, sprintf('Unsupported database platform: %s', $this->connection->getDatabasePlatform()->getName()));
    }

    public function down(Schema $schema): void
    {
        if ($this->isMySql() || $this->isPostgreSql()) {
            $this->addSql('ALTER TABLE contact_message DROP status');

            return;
        }

        $this->abortIf(true, sprintf('Unsupported database platform: %s', $this->connection->getDatabasePlatform()->getName()));
    }
}
