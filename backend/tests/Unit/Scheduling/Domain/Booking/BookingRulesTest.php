<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Booking;

use App\Scheduling\Domain\Availability\OpeningHours;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use App\Scheduling\Domain\Booking\DoesNotOverlap;
use App\Scheduling\Domain\Booking\RespectsMinimumNotice;
use App\Scheduling\Domain\Booking\WithinOpeningHours;
use App\Tests\Double\FixedClock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BookingRulesTest extends TestCase
{
    public function testWithinOpeningHours(): void
    {
        $rule = new WithinOpeningHours(new WeeklySchedule(OpeningHours::on(1, '09:00', '12:00')));

        self::assertTrue($rule->isSatisfiedBy(self::slot('09:00', '10:00')));
        self::assertFalse($rule->isSatisfiedBy(self::slot('11:30', '12:30')));
    }

    #[DataProvider('overlapCases')]
    public function testDoesNotOverlap(string $start, string $end, bool $expected): void
    {
        $rule = new DoesNotOverlap([self::slot('09:00', '10:00'), self::slot('11:00', '12:00')]);

        self::assertSame($expected, $rule->isSatisfiedBy(self::slot($start, $end)));
    }

    /** @return iterable<string, array{string, string, bool}> */
    public static function overlapCases(): iterable
    {
        yield 'entre deux' => ['10:00', '11:00', true];
        yield 'sur le premier' => ['09:30', '10:30', false];
        yield 'sur le second' => ['10:30', '11:30', false];
    }

    public function testDoesNotOverlapWithNothingTaken(): void
    {
        self::assertTrue((new DoesNotOverlap([]))->isSatisfiedBy(self::slot('09:00', '10:00')));
    }

    #[DataProvider('noticeCases')]
    public function testRespectsMinimumNotice(string $start, bool $expected): void
    {
        $rule = new RespectsMinimumNotice(FixedClock::at('2026-01-05 08:00'), 60);

        self::assertSame($expected, $rule->isSatisfiedBy(self::slot($start, '11:00')));
    }

    /** @return iterable<string, array{string, bool}> */
    public static function noticeCases(): iterable
    {
        yield 'pile au délai' => ['09:00', true];
        yield 'après le délai' => ['10:00', true];
        yield 'une minute trop tôt' => ['08:59', false];
    }

    private static function slot(string $start, string $end): TimeSlot
    {
        return TimeSlot::between(new \DateTimeImmutable('2026-01-05 '.$start), new \DateTimeImmutable('2026-01-05 '.$end));
    }
}
