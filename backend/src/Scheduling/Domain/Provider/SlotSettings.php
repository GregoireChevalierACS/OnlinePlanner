<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Provider;

use App\Scheduling\Domain\Availability\FixedIntervalStrategy;
use App\Scheduling\Domain\Availability\SlotGenerationStrategy;
use App\Scheduling\Domain\Exception\InvalidProvider;

/**
 * Value Object : réglages de prise de rendez-vous d'un prestataire.
 *
 * Seules les valeurs scalaires sont stockées : la persistance reconstruit l'objet
 * sans passer par le constructeur, la stratégie est donc recalculée à la demande.
 */
final readonly class SlotSettings
{
    public function __construct(
        public int $durationMinutes,
        public int $bufferMinutes = 0,
        public int $minimumNoticeMinutes = 0,
    ) {
        $this->strategy();
        if ($minimumNoticeMinutes < 0) {
            throw InvalidProvider::negativeNotice($minimumNoticeMinutes);
        }
    }

    public function strategy(): SlotGenerationStrategy
    {
        return new FixedIntervalStrategy($this->durationMinutes, $this->bufferMinutes);
    }
}
