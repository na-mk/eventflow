<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add end date to event table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ADD end_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP end_date');
    }
}
