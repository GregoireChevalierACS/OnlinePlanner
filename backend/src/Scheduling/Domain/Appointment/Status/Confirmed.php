<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment\Status;

final readonly class Confirmed extends BaseStatus
{
    public const string NAME = 'confirmed';

    public function name(): string
    {
        return self::NAME;
    }

    public function cancel(): AppointmentStatus
    {
        return new Cancelled();
    }

    public function complete(): AppointmentStatus
    {
        return new Completed();
    }
}
