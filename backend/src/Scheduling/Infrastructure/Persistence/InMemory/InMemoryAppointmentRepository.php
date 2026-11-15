<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Persistence\InMemory;

use App\Scheduling\Domain\Appointment\Appointment;
use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Exception\AppointmentNotFound;
use App\Scheduling\Domain\Exception\SlotUnavailable;
use App\Scheduling\Domain\Provider\Provider;
use App\Scheduling\Domain\ProviderId;

/**
 * Stocke des copies, comme une vraie base : un `save()` oublié se voit dans les tests.
 */
final class InMemoryAppointmentRepository implements AppointmentRepository
{
    /** @var array<string, Appointment> */
    private array $appointments = [];

    public function save(Appointment $appointment): void
    {
        if ($this->conflictsWithAnother($appointment)) {
            throw SlotUnavailable::because([Provider::RULE_ALREADY_TAKEN]);
        }
        $copy = clone $appointment;
        $copy->pullDomainEvents();
        $this->appointments[$appointment->id()->value] = $copy;
    }

    public function get(AppointmentId $id): Appointment
    {
        return clone ($this->appointments[$id->value] ?? throw AppointmentNotFound::withId($id));
    }

    public function overlapping(ProviderId $providerId, TimeSlot $period): array
    {
        $matching = [];
        foreach ($this->appointments as $appointment) {
            if ($appointment->providerId()->equals($providerId) && $appointment->slot()->overlaps($period)) {
                $matching[] = clone $appointment;
            }
        }
        usort($matching, static fn (Appointment $a, Appointment $b): int => $a->slot()->start <=> $b->slot()->start);

        return $matching;
    }

    /** Équivalent de la contrainte d'exclusion PostgreSQL de l'adapter Doctrine. */
    private function conflictsWithAnother(Appointment $appointment): bool
    {
        if (!$appointment->blocksSlot()) {
            return false;
        }
        foreach ($this->appointments as $other) {
            if (!$other->id()->equals($appointment->id())
                && $other->blocksSlot()
                && $other->providerId()->equals($appointment->providerId())
                && $other->slot()->overlaps($appointment->slot())) {
                return true;
            }
        }

        return false;
    }
}
