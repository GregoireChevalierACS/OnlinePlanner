<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Command;

use App\Shared\Application\Command;

final readonly class CancelAppointment implements Command
{
    public function __construct(
        public string $appointmentId,
        public string $reason,
    ) {
    }
}
