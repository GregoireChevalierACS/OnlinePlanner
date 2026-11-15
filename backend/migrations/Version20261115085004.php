<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Tables prestataire et rendez-vous, et contrainte d'exclusion anti double réservation.
 */
final class Version20261115085004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tables provider et appointment, contrainte d\'exclusion anti double réservation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE appointment (
              provider_id VARCHAR(36) NOT NULL,
              status VARCHAR(20) NOT NULL,
              id VARCHAR(36) NOT NULL,
              customer_name VARCHAR(255) NOT NULL,
              customer_email VARCHAR(320) NOT NULL,
              slot_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              slot_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX appointment_provider_start_idx ON appointment (provider_id, slot_start)');

        // Non exprimable dans le mapping : deux rendez-vous actifs d'un même prestataire
        // ne peuvent pas se chevaucher, même en cas de requêtes simultanées (ADR 0003).
        $this->addSql('CREATE EXTENSION IF NOT EXISTS btree_gist');
        $this->addSql(<<<'SQL'
            ALTER TABLE appointment ADD CONSTRAINT appointment_no_overlap
              EXCLUDE USING gist (provider_id WITH =, tsrange(slot_start, slot_end) WITH &&)
              WHERE (status <> 'cancelled')
        SQL);
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT appointment_slot_order CHECK (slot_end > slot_start)');
        $this->addSql(<<<'SQL'
            CREATE TABLE provider (
              name VARCHAR(255) NOT NULL,
              schedule JSON NOT NULL,
              id VARCHAR(36) NOT NULL,
              slot_duration_minutes SMALLINT NOT NULL,
              slot_buffer_minutes SMALLINT NOT NULL,
              minimum_notice_minutes INT NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE provider');
    }
}
