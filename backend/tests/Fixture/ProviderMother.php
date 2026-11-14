<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Scheduling\Domain\Availability\OpeningHours;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use App\Scheduling\Domain\Provider\Provider;
use App\Scheduling\Domain\Provider\SlotSettings;
use App\Scheduling\Domain\ProviderId;

/**
 * Object Mother : des prestataires prêts à l'emploi pour les tests.
 */
final class ProviderMother
{
    /** Lundi 2026-01-05, 09:00–12:00, créneaux de 30 min, prévenance 60 min. */
    public const string MONDAY = '2026-01-05';

    public static function doctor(string $id = 'p1', int $minimumNoticeMinutes = 60): Provider
    {
        return Provider::register(
            new ProviderId($id),
            'Dr Martin',
            new WeeklySchedule(OpeningHours::on(1, '09:00', '12:00')),
            new SlotSettings(30, 0, $minimumNoticeMinutes),
        );
    }
}
