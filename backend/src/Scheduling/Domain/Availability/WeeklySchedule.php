<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Availability;

use App\Scheduling\Domain\Exception\InvalidOpeningHours;

/**
 * Value Object : l'ensemble des plages d'ouverture d'un prestataire sur une semaine type.
 */
final readonly class WeeklySchedule
{
    /** @var list<OpeningHours> */
    public array $openingHours;

    public function __construct(OpeningHours ...$openingHours)
    {
        $sorted = array_values($openingHours);
        usort(
            $sorted,
            static fn (OpeningHours $a, OpeningHours $b): int => [$a->weekday, $a->opensAtMinute] <=> [$b->weekday, $b->opensAtMinute],
        );
        foreach ($sorted as $index => $hours) {
            $next = $sorted[$index + 1] ?? null;
            if (null !== $next && $hours->overlaps($next)) {
                throw InvalidOpeningHours::overlapping($hours->weekday);
            }
        }
        $this->openingHours = $sorted;
    }

    /**
     * @return list<TimeSlot> les fenêtres d'ouverture du jour, dans l'ordre chronologique
     */
    public function windowsOn(\DateTimeImmutable $day): array
    {
        $windows = [];
        foreach ($this->openingHours as $hours) {
            $window = $hours->windowOn($day);
            if (null !== $window) {
                $windows[] = $window;
            }
        }

        return $windows;
    }

    public function covers(TimeSlot $slot): bool
    {
        foreach ($this->windowsOn($slot->start) as $window) {
            if ($slot->isWithin($window)) {
                return true;
            }
        }

        return false;
    }
}
