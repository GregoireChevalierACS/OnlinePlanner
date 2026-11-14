<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Command;

use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Shared\Application\CommandHandler;
use App\Shared\Application\DomainEventPublisher;
use App\Shared\Domain\Clock;

final readonly class ConfirmAppointmentHandler implements CommandHandler
{
    public function __construct(
        private AppointmentRepository $appointments,
        private Clock $clock,
        private DomainEventPublisher $events,
    ) {
    }

    public function __invoke(ConfirmAppointment $command): void
    {
        $appointment = $this->appointments->get(new AppointmentId($command->appointmentId));
        $appointment->confirm($this->clock->now());

        $this->appointments->save($appointment);
        $this->events->publish(...$appointment->pullDomainEvents());
    }
}
