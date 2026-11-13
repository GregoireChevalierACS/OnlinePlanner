<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Availability;

use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Exception\InvalidTimeSlot;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TimeSlotTest extends TestCase
{
    public function testBetweenKeepsBounds(): void
    {
        $slot = self::slot('09:00', '10:30');

        self::assertEquals(new \DateTimeImmutable('2026-01-05 09:00'), $slot->start);
        self::assertEquals(new \DateTimeImmutable('2026-01-05 10:30'), $slot->end);
        self::assertSame(90, $slot->durationInMinutes());
    }

    #[DataProvider('invalidBounds')]
    public function testBetweenRejectsEndNotAfterStart(string $start, string $end): void
    {
        $this->expectException(InvalidTimeSlot::class);
        $this->expectExceptionMessage('La fin du créneau doit être postérieure à son début.');

        self::slot($start, $end);
    }

    /** @return iterable<string, array{string, string}> */
    public static function invalidBounds(): iterable
    {
        yield 'égales' => ['09:00', '09:00'];
        yield 'inversées' => ['10:00', '09:00'];
    }

    public function testStartingAtComputesEnd(): void
    {
        $slot = TimeSlot::startingAt(new \DateTimeImmutable('2026-01-05 09:00'), 1);

        self::assertEquals(new \DateTimeImmutable('2026-01-05 09:01'), $slot->end);
    }

    #[DataProvider('nonPositiveDurations')]
    public function testStartingAtRejectsNonPositiveDuration(int $minutes): void
    {
        $this->expectException(InvalidTimeSlot::class);
        $this->expectExceptionMessage(\sprintf('positive, %d reçu', $minutes));

        TimeSlot::startingAt(new \DateTimeImmutable(), $minutes);
    }

    /** @return iterable<array{int}> */
    public static function nonPositiveDurations(): iterable
    {
        yield [0];
        yield [-15];
    }

    #[DataProvider('overlapCases')]
    public function testOverlaps(string $start, string $end, bool $expected): void
    {
        $reference = self::slot('09:00', '10:00');
        $other = self::slot($start, $end);

        self::assertSame($expected, $reference->overlaps($other));
        self::assertSame($expected, $other->overlaps($reference));
    }

    /** @return iterable<string, array{string, string, bool}> */
    public static function overlapCases(): iterable
    {
        yield 'contigu avant' => ['08:00', '09:00', false];
        yield 'contigu après' => ['10:00', '11:00', false];
        yield 'chevauche le début' => ['08:30', '09:01', true];
        yield 'chevauche la fin' => ['09:59', '11:00', true];
        yield 'inclus' => ['09:15', '09:45', true];
        yield 'identique' => ['09:00', '10:00', true];
    }

    #[DataProvider('withinCases')]
    public function testIsWithin(string $start, string $end, bool $expected): void
    {
        self::assertSame($expected, self::slot($start, $end)->isWithin(self::slot('09:00', '12:00')));
    }

    /** @return iterable<string, array{string, string, bool}> */
    public static function withinCases(): iterable
    {
        yield 'bornes exactes' => ['09:00', '12:00', true];
        yield 'intérieur' => ['10:00', '11:00', true];
        yield 'commence avant' => ['08:59', '10:00', false];
        yield 'finit après' => ['11:00', '12:01', false];
    }

    public function testEquality(): void
    {
        self::assertTrue(self::slot('09:00', '10:00')->equals(self::slot('09:00', '10:00')));
        self::assertFalse(self::slot('09:00', '10:00')->equals(self::slot('09:00', '10:01')));
        self::assertFalse(self::slot('09:00', '10:00')->equals(self::slot('08:59', '10:00')));
    }

    private static function slot(string $start, string $end): TimeSlot
    {
        return TimeSlot::between(
            new \DateTimeImmutable('2026-01-05 '.$start),
            new \DateTimeImmutable('2026-01-05 '.$end),
        );
    }
}
