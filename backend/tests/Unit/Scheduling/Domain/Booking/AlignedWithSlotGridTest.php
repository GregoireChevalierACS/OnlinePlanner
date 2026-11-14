<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Booking;

use App\Scheduling\Domain\Availability\FixedIntervalStrategy;
use App\Scheduling\Domain\Availability\OpeningHours;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use App\Scheduling\Domain\Booking\AlignedWithSlotGrid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AlignedWithSlotGridTest extends TestCase
{
    #[DataProvider('cases')]
    public function testOnlyGeneratedSlotsAreAccepted(string $start, int $minutes, bool $expected): void
    {
        $rule = new AlignedWithSlotGrid(
            new WeeklySchedule(OpeningHours::on(1, '09:00', '10:00'), OpeningHours::on(1, '14:00', '15:00')),
            new FixedIntervalStrategy(30),
        );

        self::assertSame($expected, $rule->isSatisfiedBy(TimeSlot::startingAt(new \DateTimeImmutable('2026-01-05 '.$start), $minutes)));
    }

    /** @return iterable<string, array{string, int, bool}> */
    public static function cases(): iterable
    {
        yield 'premier créneau' => ['09:00', 30, true];
        yield 'second créneau' => ['09:30', 30, true];
        yield 'seconde fenêtre' => ['14:30', 30, true];
        yield 'décalé' => ['09:10', 30, false];
        yield 'mauvaise durée' => ['09:00', 60, false];
    }

    public function testClosedDayIsRejected(): void
    {
        $rule = new AlignedWithSlotGrid(new WeeklySchedule(OpeningHours::on(2, '09:00', '10:00')), new FixedIntervalStrategy(30));

        self::assertFalse($rule->isSatisfiedBy(TimeSlot::startingAt(new \DateTimeImmutable('2026-01-05 09:00'), 30)));
    }
}
