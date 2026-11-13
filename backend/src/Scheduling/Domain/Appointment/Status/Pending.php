<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment\Status;

final readonly class Pending extends BaseStatus
{
    public const string NAME = 'pending';

    public function name(): string
    {
        return self::NAME;
    }

    public function confirm(): AppointmentStatus
    {
        return new Confirmed();
    }

    public function cancel(): AppointmentStatus
    {
        return new Cancelled();
    }
}
