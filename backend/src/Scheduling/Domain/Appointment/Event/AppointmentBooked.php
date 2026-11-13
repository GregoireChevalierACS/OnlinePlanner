<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment\Event;

use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\Customer;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\ProviderId;
use App\Shared\Domain\DomainEvent;

final readonly class AppointmentBooked implements DomainEvent
{
    public function __construct(
        public AppointmentId $appointmentId,
        public ProviderId $providerId,
        public Customer $customer,
        public TimeSlot $slot,
        private \DateTimeImmutable $occurredOn,
    ) {
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
