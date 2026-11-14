<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Exception;

use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Shared\Domain\NotFound;

final class AppointmentNotFound extends NotFound
{
    public static function withId(AppointmentId $id): self
    {
        return new self(\sprintf('Rendez-vous « %s » introuvable.', $id));
    }
}
