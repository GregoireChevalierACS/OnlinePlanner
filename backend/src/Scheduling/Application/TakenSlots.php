<?php

declare(strict_types=1);

namespace App\Scheduling\Application;

use App\Scheduling\Domain\Appointment\Appointment;
use App\Scheduling\Domain\Availability\TimeSlot;

/**
 * Créneaux réellement occupés : les rendez-vous annulés libèrent leur place.
 */
final class TakenSlots
{
    /**
     * @param list<Appointment> $appointments
     *
     * @return list<TimeSlot>
     */
    public static function of(array $appointments): array
    {
        $slots = [];
        foreach ($appointments as $appointment) {
            if ($appointment->blocksSlot()) {
                $slots[] = $appointment->slot();
            }
        }

        return $slots;
    }
}
