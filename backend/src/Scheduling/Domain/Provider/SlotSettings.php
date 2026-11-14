<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Provider;

use App\Scheduling\Domain\Availability\FixedIntervalStrategy;
use App\Scheduling\Domain\Availability\SlotGenerationStrategy;
use App\Scheduling\Domain\Exception\InvalidProvider;

/**
 * Value Object : réglages de prise de rendez-vous d'un prestataire.
 */
final readonly class SlotSettings
{
    private FixedIntervalStrategy $strategy;

    public function __construct(
        public int $durationMinutes,
        public int $bufferMinutes = 0,
        public int $minimumNoticeMinutes = 0,
    ) {
        $this->strategy = new FixedIntervalStrategy($durationMinutes, $bufferMinutes);
        if ($minimumNoticeMinutes < 0) {
            throw InvalidProvider::negativeNotice($minimumNoticeMinutes);
        }
    }

    public function strategy(): SlotGenerationStrategy
    {
        return $this->strategy;
    }
}
