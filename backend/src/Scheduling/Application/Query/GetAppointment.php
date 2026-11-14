<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Query;

use App\Shared\Application\Query;

final readonly class GetAppointment implements Query
{
    public function __construct(public string $appointmentId)
    {
    }
}
