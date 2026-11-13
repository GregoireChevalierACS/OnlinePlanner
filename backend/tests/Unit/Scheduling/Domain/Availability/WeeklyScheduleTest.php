<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Availability;

use App\Scheduling\Domain\Availability\OpeningHours;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use App\Scheduling\Domain\Exception\InvalidOpeningHours;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WeeklyScheduleTest extends TestCase
{
    private const string MONDAY = '2026-01-05';

    public function testSortsOpeningHoursByDayThenTime(): void
    {
        $tuesday = OpeningHours::on(2, '09:00', '12:00');
        $mondayAfternoon = OpeningHours::on(1, '14:00', '18:00');
        $mondayMorning = OpeningHours::on(1, '09:00', '12:00');

        $schedule = new WeeklySchedule($tuesday, $mondayAfternoon, $mondayMorning);

        self::assertSame([$mondayMorning, $mondayAfternoon, $tuesday], $schedule->openingHours);
    }

    public function testRejectsOverlappingHours(): void
    {
        $this->expectException(InvalidOpeningHours::class);
        $this->expectExceptionMessage('Plages d\'ouverture qui se chevauchent le jour 3.');

        new WeeklySchedule(
            OpeningHours::on(3, '14:00', '18:00'),
            OpeningHours::on(1, '09:00', '12:00'),
            OpeningHours::on(3, '09:00', '14:01'),
        );
    }

    public function testWindowsOnReturnsOnlyThatDayInOrder(): void
    {
        $schedule = new WeeklySchedule(
            OpeningHours::on(1, '14:00', '18:00'),
            OpeningHours::on(2, '09:00', '12:00'),
            OpeningHours::on(1, '09:00', '12:00'),
        );

        $windows = $schedule->windowsOn(new \DateTimeImmutable(self::MONDAY));

        self::assertCount(2, $windows);
        self::assertTrue($windows[0]->equals(self::slot('09:00', '12:00')));
        self::assertTrue($windows[1]->equals(self::slot('14:00', '18:00')));
    }

    public function testNoWindowsOnClosedDay(): void
    {
        self::assertSame([], (new WeeklySchedule(OpeningHours::on(2, '09:00', '12:00')))->windowsOn(new \DateTimeImmutable(self::MONDAY)));
    }

    #[DataProvider('coverCases')]
    public function testCovers(string $start, string $end, bool $expected): void
    {
        $schedule = new WeeklySchedule(
            OpeningHours::on(1, '09:00', '12:00'),
            OpeningHours::on(1, '14:00', '18:00'),
        );

        self::assertSame($expected, $schedule->covers(self::slot($start, $end)));
    }

    /** @return iterable<string, array{string, string, bool}> */
    public static function coverCases(): iterable
    {
        yield 'matin' => ['09:00', '10:00', true];
        yield 'après-midi' => ['17:00', '18:00', true];
        yield 'pause déjeuner' => ['12:00', '14:00', false];
        yield 'à cheval' => ['11:30', '12:30', false];
    }

    private static function slot(string $start, string $end): TimeSlot
    {
        return TimeSlot::between(
            new \DateTimeImmutable(self::MONDAY.' '.$start),
            new \DateTimeImmutable(self::MONDAY.' '.$end),
        );
    }
}
