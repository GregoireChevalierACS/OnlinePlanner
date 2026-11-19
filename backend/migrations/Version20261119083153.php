<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Vérification des adresses e-mail : liens à usage unique et date de vérification du compte.
 */
final class Version20261119083153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Vérification des adresses e-mail des comptes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE email_verification (
              account_id VARCHAR(36) NOT NULL,
              expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              token_hash VARCHAR(64) NOT NULL,
              PRIMARY KEY (token_hash)
            )
        SQL);
        $this->addSql('CREATE INDEX email_verification_account_idx ON email_verification (account_id)');
        $this->addSql('ALTER TABLE account ADD email_verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE email_verification');
        $this->addSql('ALTER TABLE account DROP email_verified_at');
    }
}
