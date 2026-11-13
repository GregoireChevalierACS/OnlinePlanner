<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Booking;

use App\Scheduling\Domain\Availability\TimeSlot;
use App\Shared\Domain\Clock;
use App\Shared\Domain\Specification;

/**
 * Délai de prévenance : on ne réserve pas un créneau qui commence dans moins de N minutes.
 *
 * @implements Specification<TimeSlot>
 */
final readonly class RespectsMinimumNotice implements Specification
{
    public function __construct(
        private Clock $clock,
        private int $minutes,
    ) {
    }

    /**
     * @param TimeSlot $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        $earliest = $this->clock->now()->modify(\sprintf('+%d minutes', $this->minutes));

        return $candidate->start >= $earliest;
    }
}
