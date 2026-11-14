<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Command;

use App\Shared\Application\Command;

final readonly class ConfirmAppointment implements Command
{
    public function __construct(public string $appointmentId)
    {
    }
}
