<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Demandes de réinitialisation de mot de passe (empreinte du jeton, expiration, usage unique).
 */
final class Version20261118084928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Demandes de réinitialisation de mot de passe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE password_reset (
              account_id VARCHAR(36) NOT NULL,
              expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              token_hash VARCHAR(64) NOT NULL,
              PRIMARY KEY (token_hash)
            )
        SQL);
        $this->addSql('CREATE INDEX password_reset_account_idx ON password_reset (account_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE password_reset');
    }
}
