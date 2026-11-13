<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Availability;

use App\Scheduling\Domain\Availability\OpeningHours;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Exception\InvalidOpeningHours;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OpeningHoursTest extends TestCase
{
    private const string MONDAY = '2026-01-05';

    public function testExposesFormattedHours(): void
    {
        $hours = OpeningHours::on(1, '09:05', '23:59');

        self::assertSame(1, $hours->weekday);
        self::assertSame('09:05', $hours->opensAt());
        self::assertSame('23:59', $hours->closesAt());
        self::assertSame(545, $hours->opensAtMinute);
        self::assertSame(1439, $hours->closesAtMinute);
    }

    #[DataProvider('validWeekdays')]
    public function testAcceptsWeekdayBounds(int $weekday): void
    {
        self::assertSame($weekday, OpeningHours::on($weekday, '00:00', '01:00')->weekday);
    }

    /** @return iterable<array{int}> */
    public static function validWeekdays(): iterable
    {
        yield [1];
        yield [7];
    }

    #[DataProvider('invalidWeekdays')]
    public function testRejectsInvalidWeekday(int $weekday): void
    {
        $this->expectException(InvalidOpeningHours::class);
        $this->expectExceptionMessage(\sprintf('Jour de semaine invalide : %d', $weekday));

        OpeningHours::on($weekday, '09:00', '12:00');
    }

    /** @return iterable<array{int}> */
    public static function invalidWeekdays(): iterable
    {
        yield [0];
        yield [8];
    }

    #[DataProvider('invalidTimes')]
    public function testRejectsInvalidTime(string $time): void
    {
        $this->expectException(InvalidOpeningHours::class);
        $this->expectExceptionMessage(\sprintf('Heure invalide : « %s »', $time));

        OpeningHours::on(1, $time, '23:59');
    }

    /** @return iterable<array{string}> */
    public static function invalidTimes(): iterable
    {
        yield ['24:00'];
        yield ['9:00'];
        yield ['09:60'];
        yield ['x09:00'];
        yield ['09:00x'];
    }

    #[DataProvider('closingNotAfterOpening')]
    public function testRejectsClosingNotAfterOpening(string $closes): void
    {
        $this->expectException(InvalidOpeningHours::class);
        $this->expectExceptionMessage('L\'heure de fermeture doit être postérieure à l\'heure d\'ouverture.');

        OpeningHours::on(1, '09:00', $closes);
    }

    /** @return iterable<array{string}> */
    public static function closingNotAfterOpening(): iterable
    {
        yield ['09:00'];
        yield ['08:59'];
    }

    public function testWindowOnMatchingDay(): void
    {
        $window = OpeningHours::on(1, '09:30', '12:15')->windowOn(new \DateTimeImmutable(self::MONDAY.' 18:42'));

        self::assertNotNull($window);
        self::assertTrue($window->equals(TimeSlot::between(
            new \DateTimeImmutable(self::MONDAY.' 09:30'),
            new \DateTimeImmutable(self::MONDAY.' 12:15'),
        )));
    }

    public function testWindowLateInTheDay(): void
    {
        $window = OpeningHours::on(1, '23:40', '23:59')->windowOn(new \DateTimeImmutable(self::MONDAY));

        self::assertNotNull($window);
        self::assertSame('2026-01-05 23:40', $window->start->format('Y-m-d H:i'));
        self::assertSame('2026-01-05 23:59', $window->end->format('Y-m-d H:i'));
    }

    public function testNoWindowOnOtherDay(): void
    {
        self::assertNull(OpeningHours::on(2, '09:00', '12:00')->windowOn(new \DateTimeImmutable(self::MONDAY)));
    }

    #[DataProvider('overlapCases')]
    public function testOverlaps(int $weekday, string $opens, string $closes, bool $expected): void
    {
        $reference = OpeningHours::on(1, '09:00', '12:00');
        $other = OpeningHours::on($weekday, $opens, $closes);

        self::assertSame($expected, $reference->overlaps($other));
        self::assertSame($expected, $other->overlaps($reference));
    }

    /** @return iterable<string, array{int, string, string, bool}> */
    public static function overlapCases(): iterable
    {
        yield 'même jour, chevauchement' => [1, '11:59', '14:00', true];
        yield 'même jour, contigu' => [1, '12:00', '14:00', false];
        yield 'même jour, avant' => [1, '07:00', '09:00', false];
        yield 'autre jour' => [2, '09:00', '12:00', false];
    }
}
