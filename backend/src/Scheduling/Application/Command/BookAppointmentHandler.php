<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Command;

use App\Scheduling\Application\TakenSlots;
use App\Scheduling\Domain\Appointment\Appointment;
use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Domain\Appointment\Customer;
use App\Scheduling\Domain\Appointment\Email;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Scheduling\Domain\ProviderId;
use App\Shared\Application\CommandHandler;
use App\Shared\Application\DomainEventPublisher;
use App\Shared\Domain\Clock;

final readonly class BookAppointmentHandler implements CommandHandler
{
    public function __construct(
        private ProviderRepository $providers,
        private AppointmentRepository $appointments,
        private Clock $clock,
        private DomainEventPublisher $events,
    ) {
    }

    public function __invoke(BookAppointment $command): void
    {
        $provider = $this->providers->get(new ProviderId($command->providerId));
        $slot = $provider->slotStartingAt($command->startsAt);

        $appointment = Appointment::book(
            new AppointmentId($command->appointmentId),
            $provider->id(),
            new Customer($command->customerName, new Email($command->customerEmail)),
            $slot,
            $provider->bookingPolicy($this->clock, TakenSlots::of($this->appointments->overlapping($provider->id(), $slot))),
            $this->clock->now(),
        );

        $this->appointments->save($appointment);
        $this->events->publish(...$appointment->pullDomainEvents());
    }
}
