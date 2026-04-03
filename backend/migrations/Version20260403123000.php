<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403123000 extends AbstractMigration
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
        return 'Create contact_message table';
    }

    public function up(Schema $schema): void
    {
        if ($this->isMySql()) {
            $this->addSql("CREATE TABLE contact_message (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, email VARCHAR(180) NOT NULL, subject VARCHAR(180) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

            return;
        }

        if ($this->isPostgreSql()) {
            $this->addSql('CREATE SEQUENCE contact_message_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
            $this->addSql('CREATE TABLE contact_message (id INT NOT NULL, name VARCHAR(120) NOT NULL, email VARCHAR(180) NOT NULL, subject VARCHAR(180) NOT NULL, message TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
            $this->addSql("ALTER TABLE contact_message ALTER id SET DEFAULT nextval('contact_message_id_seq')");

            return;
        }

        $this->abortIf(true, sprintf('Unsupported database platform: %s', $this->connection->getDatabasePlatform()->getName()));
    }

    public function down(Schema $schema): void
    {
        if ($this->isMySql()) {
            $this->addSql('DROP TABLE contact_message');

            return;
        }

        if ($this->isPostgreSql()) {
            $this->addSql('DROP TABLE contact_message');
            $this->addSql('DROP SEQUENCE contact_message_id_seq');

            return;
        }

        $this->abortIf(true, sprintf('Unsupported database platform: %s', $this->connection->getDatabasePlatform()->getName()));
    }
}
