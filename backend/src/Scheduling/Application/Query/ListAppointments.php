<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Query;

use App\Shared\Application\Query;

/**
 * Agenda du prestataire entre deux dates (incluses), tous statuts confondus.
 */
final readonly class ListAppointments implements Query
{
    public function __construct(
        public string $providerId,
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
    ) {
    }
}
