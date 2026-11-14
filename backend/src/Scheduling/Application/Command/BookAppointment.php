<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Command;

use App\Shared\Application\Command;

final readonly class BookAppointment implements Command
{
    public function __construct(
        public string $appointmentId,
        public string $providerId,
        public \DateTimeImmutable $startsAt,
        public string $customerName,
        public string $customerEmail,
    ) {
    }
}
