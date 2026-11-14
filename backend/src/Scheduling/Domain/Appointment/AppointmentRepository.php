<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment;

use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Exception\AppointmentNotFound;
use App\Scheduling\Domain\ProviderId;

/**
 * Port Repository des rendez-vous.
 */
interface AppointmentRepository
{
    public function save(Appointment $appointment): void;

    /**
     * @throws AppointmentNotFound
     */
    public function get(AppointmentId $id): Appointment;

    /**
     * Tous les rendez-vous du prestataire (quel que soit leur statut) qui chevauchent la période,
     * triés par heure de début.
     *
     * @return list<Appointment>
     */
    public function overlapping(ProviderId $providerId, TimeSlot $period): array;
}
