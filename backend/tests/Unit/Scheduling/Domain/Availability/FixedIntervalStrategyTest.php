<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Availability;

use App\Scheduling\Domain\Availability\FixedIntervalStrategy;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Exception\InvalidSlotStrategy;
use PHPUnit\Framework\TestCase;

final class FixedIntervalStrategyTest extends TestCase
{
    public function testSplitsWindowIntoBackToBackSlots(): void
    {
        $strategy = new FixedIntervalStrategy(30);

        self::assertSame(0, $strategy->bufferMinutes);
        self::assertSame(['09:00-09:30', '09:30-10:00'], self::format($strategy->generate(self::window('09:00', '10:00'))));
    }

    public function testAppliesBufferAndDropsSlotOverflowingWindow(): void
    {
        $strategy = new FixedIntervalStrategy(30, 15);

        self::assertSame(
            ['09:00-09:30', '09:45-10:15'],
            self::format($strategy->generate(self::window('09:00', '10:59'))),
        );
    }

    public function testReturnsNothingWhenWindowTooShort(): void
    {
        self::assertSame([], (new FixedIntervalStrategy(60))->generate(self::window('09:00', '09:59')));
    }

    public function testRejectsNonPositiveDuration(): void
    {
        $this->expectException(InvalidSlotStrategy::class);
        $this->expectExceptionMessage('positive, 0 reçu');

        new FixedIntervalStrategy(0);
    }

    public function testRejectsNegativeBuffer(): void
    {
        $this->expectException(InvalidSlotStrategy::class);
        $this->expectExceptionMessage('négatif, -1 reçu');

        new FixedIntervalStrategy(30, -1);
    }

    public function testAcceptsMinimalValues(): void
    {
        $strategy = new FixedIntervalStrategy(1, 0);

        self::assertSame(1, $strategy->durationMinutes);
        self::assertCount(2, $strategy->generate(self::window('09:00', '09:02')));
    }

    private static function window(string $start, string $end): TimeSlot
    {
        return TimeSlot::between(new \DateTimeImmutable('2026-01-05 '.$start), new \DateTimeImmutable('2026-01-05 '.$end));
    }

    /**
     * @param list<TimeSlot> $slots
     *
     * @return list<string>
     */
    private static function format(array $slots): array
    {
        return array_map(static fn (TimeSlot $s): string => $s->start->format('H:i').'-'.$s->end->format('H:i'), $slots);
    }
}
