<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Query;

use App\Scheduling\Domain\Appointment\Appointment;

final readonly class AppointmentView
{
    public function __construct(
        public string $id,
        public string $providerId,
        public string $customerName,
        public string $customerEmail,
        public string $startsAt,
        public string $endsAt,
        public string $status,
    ) {
    }

    public static function fromAppointment(Appointment $appointment): self
    {
        return new self(
            $appointment->id()->value,
            $appointment->providerId()->value,
            $appointment->customer()->name,
            $appointment->customer()->email->value,
            $appointment->slot()->start->format(\DATE_ATOM),
            $appointment->slot()->end->format(\DATE_ATOM),
            $appointment->status()->name(),
        );
    }
}
