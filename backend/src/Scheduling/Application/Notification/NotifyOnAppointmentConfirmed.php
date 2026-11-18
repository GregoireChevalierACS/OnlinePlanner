<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Notification;

use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Domain\Appointment\Event\AppointmentConfirmed;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Shared\Application\EventHandler;
use App\Shared\Application\Notification\Notifier;

final readonly class NotifyOnAppointmentConfirmed implements EventHandler
{
    public function __construct(
        private AppointmentRepository $appointments,
        private ProviderRepository $providers,
        private Notifier $notifier,
    ) {
    }

    public function __invoke(AppointmentConfirmed $event): void
    {
        $appointment = $this->appointments->get($event->appointmentId);
        $provider = $this->providers->get($appointment->providerId());

        $this->notifier->send(AppointmentEmails::confirmed($appointment->customer(), $provider->name(), $appointment->slot()));
    }
}
