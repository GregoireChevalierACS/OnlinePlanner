<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Availability;

use App\Scheduling\Domain\Exception\InvalidSlotStrategy;

/**
 * Créneaux de durée fixe, séparés par un temps de battement optionnel
 * (ex. 30 min + 10 min de pause entre deux rendez-vous).
 */
final readonly class FixedIntervalStrategy implements SlotGenerationStrategy
{
    public function __construct(
        public int $durationMinutes,
        public int $bufferMinutes = 0,
    ) {
        if ($durationMinutes <= 0) {
            throw InvalidSlotStrategy::nonPositiveDuration($durationMinutes);
        }
        if ($bufferMinutes < 0) {
            throw InvalidSlotStrategy::negativeBuffer($bufferMinutes);
        }
    }

    public function generate(TimeSlot $window): array
    {
        $slots = [];
        $cursor = $window->start;
        while (true) {
            $slot = TimeSlot::startingAt($cursor, $this->durationMinutes);
            if (!$slot->isWithin($window)) {
                return $slots;
            }
            $slots[] = $slot;
            $cursor = $slot->end->modify(\sprintf('+%d minutes', $this->bufferMinutes));
        }
    }
}
