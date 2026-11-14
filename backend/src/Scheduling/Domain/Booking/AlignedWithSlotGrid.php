<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Booking;

use App\Scheduling\Domain\Availability\SlotGenerationStrategy;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use App\Shared\Domain\Specification;

/**
 * Le créneau demandé doit être l'un de ceux que la Strategy du prestataire propose
 * (pas de rendez-vous à 09:07 si l'agenda est découpé en demi-heures).
 *
 * @implements Specification<TimeSlot>
 */
final readonly class AlignedWithSlotGrid implements Specification
{
    public function __construct(
        private WeeklySchedule $schedule,
        private SlotGenerationStrategy $strategy,
    ) {
    }

    /**
     * @param TimeSlot $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        foreach ($this->schedule->windowsOn($candidate->start) as $window) {
            foreach ($this->strategy->generate($window) as $slot) {
                if ($slot->equals($candidate)) {
                    return true;
                }
            }
        }

        return false;
    }
}
