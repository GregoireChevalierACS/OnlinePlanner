<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Availability;

use App\Shared\Domain\Specification;

/**
 * Domain Service : créneaux libres d'une journée = fenêtres d'ouverture
 * découpées par la Strategy, puis filtrées par la politique de réservation.
 */
final readonly class AvailabilityCalculator
{
    public function __construct(private SlotGenerationStrategy $strategy)
    {
    }

    /**
     * @param Specification<TimeSlot> $policy
     *
     * @return list<TimeSlot>
     */
    public function availableSlots(WeeklySchedule $schedule, \DateTimeImmutable $day, Specification $policy): array
    {
        $available = [];
        foreach ($schedule->windowsOn($day) as $window) {
            foreach ($this->strategy->generate($window) as $slot) {
                if ($policy->isSatisfiedBy($slot)) {
                    $available[] = $slot;
                }
            }
        }

        return $available;
    }
}
