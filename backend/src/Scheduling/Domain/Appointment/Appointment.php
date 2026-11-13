<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment;

use App\Scheduling\Domain\Appointment\Event\AppointmentBooked;
use App\Scheduling\Domain\Appointment\Event\AppointmentCancelled;
use App\Scheduling\Domain\Appointment\Event\AppointmentConfirmed;
use App\Scheduling\Domain\Appointment\Status\AppointmentStatus;
use App\Scheduling\Domain\Appointment\Status\Pending;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Booking\BookingPolicy;
use App\Scheduling\Domain\Exception\InvalidStatusTransition;
use App\Scheduling\Domain\ProviderId;
use App\Shared\Domain\AggregateRoot;

/**
 * Aggregate Root : un rendez-vous. Il protège ses invariants (règles de réservation,
 * transitions de statut) et enregistre ses Domain Events.
 */
final class Appointment extends AggregateRoot
{
    private function __construct(
        private readonly AppointmentId $id,
        private readonly ProviderId $providerId,
        private readonly Customer $customer,
        private readonly TimeSlot $slot,
        private AppointmentStatus $status,
    ) {
    }

    /**
     * Factory Method : la seule façon de créer un rendez-vous passe par les règles de réservation.
     */
    public static function book(
        AppointmentId $id,
        ProviderId $providerId,
        Customer $customer,
        TimeSlot $slot,
        BookingPolicy $policy,
        \DateTimeImmutable $now,
    ): self {
        $policy->assertSatisfiedBy($slot);

        $appointment = new self($id, $providerId, $customer, $slot, new Pending());
        $appointment->recordThat(new AppointmentBooked($id, $providerId, $customer, $slot, $now));

        return $appointment;
    }

    public function confirm(\DateTimeImmutable $now): void
    {
        $this->status = $this->status->confirm();
        $this->recordThat(new AppointmentConfirmed($this->id, $now));
    }

    public function cancel(string $reason, \DateTimeImmutable $now): void
    {
        $this->status = $this->status->cancel();
        $this->recordThat(new AppointmentCancelled($this->id, $reason, $now));
    }

    public function complete(\DateTimeImmutable $now): void
    {
        if ($now < $this->slot->end) {
            throw InvalidStatusTransition::notFinishedYet();
        }
        $this->status = $this->status->complete();
    }

    public function blocksSlot(): bool
    {
        return $this->status->blocksSlot();
    }

    public function id(): AppointmentId
    {
        return $this->id;
    }

    public function providerId(): ProviderId
    {
        return $this->providerId;
    }

    public function customer(): Customer
    {
        return $this->customer;
    }

    public function slot(): TimeSlot
    {
        return $this->slot;
    }

    public function status(): AppointmentStatus
    {
        return $this->status;
    }
}
