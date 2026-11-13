<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Booking;

use App\Scheduling\Domain\Availability\TimeSlot;
use App\Shared\Domain\Specification;

/**
 * @implements Specification<TimeSlot>
 */
final readonly class DoesNotOverlap implements Specification
{
    /**
     * @param list<TimeSlot> $takenSlots créneaux déjà occupés
     */
    public function __construct(private array $takenSlots)
    {
    }

    /**
     * @param TimeSlot $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        foreach ($this->takenSlots as $taken) {
            if ($taken->overlaps($candidate)) {
                return false;
            }
        }

        return true;
    }
}
