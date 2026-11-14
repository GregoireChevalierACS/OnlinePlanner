<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Query;

use App\Scheduling\Domain\Availability\TimeSlot;

/**
 * DTO de lecture : ce que l'interface affiche, sans exposer le domaine.
 */
final readonly class SlotView
{
    public function __construct(
        public string $startsAt,
        public string $endsAt,
    ) {
    }

    public static function fromSlot(TimeSlot $slot): self
    {
        return new self($slot->start->format(\DATE_ATOM), $slot->end->format(\DATE_ATOM));
    }
}
