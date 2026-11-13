<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Availability;

use App\Scheduling\Domain\Exception\InvalidOpeningHours;

/**
 * Value Object : une plage d'ouverture hebdomadaire (ex. lundi 09:00–12:00),
 * exprimée en heure locale du prestataire.
 */
final readonly class OpeningHours
{
    private function __construct(
        public int $weekday,
        public int $opensAtMinute,
        public int $closesAtMinute,
    ) {
    }

    /**
     * @param int $weekday 1 = lundi … 7 = dimanche (ISO-8601)
     */
    public static function on(int $weekday, string $opensAt, string $closesAt): self
    {
        if ($weekday < 1 || $weekday > 7) {
            throw InvalidOpeningHours::weekday($weekday);
        }
        $opens = self::toMinutes($opensAt);
        $closes = self::toMinutes($closesAt);
        if ($closes <= $opens) {
            throw InvalidOpeningHours::closesBeforeOpening();
        }

        return new self($weekday, $opens, $closes);
    }

    public function windowOn(\DateTimeImmutable $day): ?TimeSlot
    {
        if ((int) $day->format('N') !== $this->weekday) {
            return null;
        }

        return TimeSlot::between(
            $day->setTime(intdiv($this->opensAtMinute, 60), $this->opensAtMinute % 60),
            $day->setTime(intdiv($this->closesAtMinute, 60), $this->closesAtMinute % 60),
        );
    }

    public function overlaps(self $other): bool
    {
        return $this->weekday === $other->weekday
            && $this->opensAtMinute < $other->closesAtMinute
            && $other->opensAtMinute < $this->closesAtMinute;
    }

    public function opensAt(): string
    {
        return self::toTime($this->opensAtMinute);
    }

    public function closesAt(): string
    {
        return self::toTime($this->closesAtMinute);
    }

    private static function toMinutes(string $time): int
    {
        if (1 !== preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time, $matches)) {
            throw InvalidOpeningHours::time($time);
        }

        return (int) $matches[1] * 60 + (int) $matches[2];
    }

    private static function toTime(int $minutes): string
    {
        return \sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
