<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment\Status;

use App\Scheduling\Domain\Exception\InvalidStatusTransition;

/**
 * Factory : reconstruit un état à partir de son nom persisté.
 */
final class AppointmentStatuses
{
    public static function fromName(string $name): AppointmentStatus
    {
        return match ($name) {
            Pending::NAME => new Pending(),
            Confirmed::NAME => new Confirmed(),
            Cancelled::NAME => new Cancelled(),
            Completed::NAME => new Completed(),
            default => throw InvalidStatusTransition::unknownStatus($name),
        };
    }
}
