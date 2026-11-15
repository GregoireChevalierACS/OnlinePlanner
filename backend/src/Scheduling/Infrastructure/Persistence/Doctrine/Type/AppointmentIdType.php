<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Persistence\Doctrine\Type;

use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Shared\Infrastructure\Persistence\Doctrine\IdentifierType;

final class AppointmentIdType extends IdentifierType
{
    public const string NAME = 'appointment_id';

    protected function identifierClass(): string
    {
        return AppointmentId::class;
    }
}
