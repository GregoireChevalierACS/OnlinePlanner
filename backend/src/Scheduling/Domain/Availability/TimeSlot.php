<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Availability;

use App\Scheduling\Domain\Exception\InvalidTimeSlot;

/**
 * Value Object : intervalle [début, fin[ immuable.
 */
final readonly class TimeSlot
{
    private function __construct(
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end,
    ) {
    }

    public static function between(\DateTimeImmutable $start, \DateTimeImmutable $end): self
    {
        if ($end <= $start) {
            throw InvalidTimeSlot::endNotAfterStart();
        }

        return new self($start, $end);
    }

    public static function startingAt(\DateTimeImmutable $start, int $minutes): self
    {
        if ($minutes <= 0) {
            throw InvalidTimeSlot::nonPositiveDuration($minutes);
        }

        return new self($start, $start->modify(\sprintf('+%d minutes', $minutes)));
    }

    public function overlaps(self $other): bool
    {
        return $this->start < $other->end && $other->start < $this->end;
    }

    public function isWithin(self $other): bool
    {
        return $this->start >= $other->start && $this->end <= $other->end;
    }

    public function durationInMinutes(): int
    {
        return intdiv($this->end->getTimestamp() - $this->start->getTimestamp(), 60);
    }

    public function equals(self $other): bool
    {
        return $this->start == $other->start && $this->end == $other->end;
    }
}
