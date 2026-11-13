<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Booking;

use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use App\Shared\Domain\Specification;

/**
 * @implements Specification<TimeSlot>
 */
final readonly class WithinOpeningHours implements Specification
{
    public function __construct(private WeeklySchedule $schedule)
    {
    }

    /**
     * @param TimeSlot $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $this->schedule->covers($candidate);
    }
}
