<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Notification;

use App\Scheduling\Domain\Appointment\Event\AppointmentBooked;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Shared\Application\EventHandler;
use App\Shared\Application\Notification\Notifier;

/**
 * Observer : accusé de réception au client et alerte au propriétaire de l'agenda.
 */
final readonly class NotifyOnAppointmentBooked implements EventHandler
{
    public function __construct(
        private ProviderRepository $providers,
        private OwnerDirectory $owners,
        private Notifier $notifier,
    ) {
    }

    public function __invoke(AppointmentBooked $event): void
    {
        $provider = $this->providers->get($event->providerId);

        $this->notifier->send(AppointmentEmails::bookingReceived($event->customer, $provider->name(), $event->slot));

        $ownerEmail = $this->owners->emailOf($provider->ownerId());
        if (null !== $ownerEmail) {
            $this->notifier->send(AppointmentEmails::newBookingForOwner($ownerEmail, $event->customer, $provider->name(), $event->slot));
        }
    }
}
