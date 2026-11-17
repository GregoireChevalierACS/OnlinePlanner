<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Comptes prestataires (adresse unique) et propriétaire de chaque agenda.
 */
final class Version20261117100325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Comptes prestataires et propriétaire de chaque agenda';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE account (
              email VARCHAR(320) NOT NULL,
              password_hash VARCHAR(255) NOT NULL,
              id VARCHAR(36) NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX account_email_unique ON account (email)');
        // Les agendas créés avant l'authentification n'ont pas de propriétaire :
        // ils restent en base, mais plus aucun compte ne peut les gérer.
        $this->addSql("ALTER TABLE provider ADD owner_id VARCHAR(36) DEFAULT 'sans-proprietaire' NOT NULL");
        $this->addSql('ALTER TABLE provider ALTER owner_id DROP DEFAULT');
        $this->addSql('CREATE INDEX provider_owner_idx ON provider (owner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP INDEX provider_owner_idx');
        $this->addSql('ALTER TABLE provider DROP owner_id');
    }
}
