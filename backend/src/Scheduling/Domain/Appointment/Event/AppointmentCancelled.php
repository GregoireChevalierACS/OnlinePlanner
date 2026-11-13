<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment\Event;

use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Shared\Domain\DomainEvent;

final readonly class AppointmentCancelled implements DomainEvent
{
    public function __construct(
        public AppointmentId $appointmentId,
        public string $reason,
        private \DateTimeImmutable $occurredOn,
    ) {
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
