<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Availability;

use App\Scheduling\Domain\Availability\AvailabilityCalculator;
use App\Scheduling\Domain\Availability\FixedIntervalStrategy;
use App\Scheduling\Domain\Availability\OpeningHours;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use App\Scheduling\Domain\Booking\BookingPolicy;
use App\Scheduling\Domain\Booking\DoesNotOverlap;
use PHPUnit\Framework\TestCase;

final class AvailabilityCalculatorTest extends TestCase
{
    public function testGeneratesSlotsOfEveryWindowAndFiltersTakenOnes(): void
    {
        $schedule = new WeeklySchedule(
            OpeningHours::on(1, '09:00', '10:00'),
            OpeningHours::on(1, '14:00', '15:00'),
        );
        $taken = TimeSlot::startingAt(new \DateTimeImmutable('2026-01-05 09:30'), 30);
        $policy = new BookingPolicy(['déjà pris' => new DoesNotOverlap([$taken])]);

        $slots = (new AvailabilityCalculator(new FixedIntervalStrategy(30)))
            ->availableSlots($schedule, new \DateTimeImmutable('2026-01-05'), $policy);

        self::assertSame(
            ['09:00', '14:00', '14:30'],
            array_map(static fn (TimeSlot $s): string => $s->start->format('H:i'), $slots),
        );
    }

    public function testClosedDayHasNoSlots(): void
    {
        $slots = (new AvailabilityCalculator(new FixedIntervalStrategy(30)))
            ->availableSlots(new WeeklySchedule(OpeningHours::on(2, '09:00', '10:00')), new \DateTimeImmutable('2026-01-05'), new BookingPolicy([]));

        self::assertSame([], $slots);
    }
}
