<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Booking;

use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Booking\BookingPolicy;
use App\Scheduling\Domain\Exception\SlotUnavailable;
use App\Shared\Domain\Specification;
use PHPUnit\Framework\TestCase;

final class BookingPolicyTest extends TestCase
{
    public function testSatisfiedWhenAllRulesPass(): void
    {
        $policy = new BookingPolicy(['a' => self::rule(true), 'b' => self::rule(true)]);

        self::assertSame([], $policy->violations(self::slot()));
        self::assertTrue($policy->isSatisfiedBy(self::slot()));
        $policy->assertSatisfiedBy(self::slot());
    }

    public function testEmptyPolicyAcceptsEverything(): void
    {
        self::assertTrue((new BookingPolicy([]))->isSatisfiedBy(self::slot()));
    }

    public function testListsEveryFailingRuleInOrder(): void
    {
        $policy = new BookingPolicy([
            'hors horaires' => self::rule(false),
            'ok' => self::rule(true),
            'déjà pris' => self::rule(false),
        ]);

        self::assertSame(['hors horaires', 'déjà pris'], $policy->violations(self::slot()));
        self::assertFalse($policy->isSatisfiedBy(self::slot()));
    }

    public function testAssertThrowsWithReasons(): void
    {
        $policy = new BookingPolicy(['hors horaires' => self::rule(false), 'déjà pris' => self::rule(false)]);

        try {
            $policy->assertSatisfiedBy(self::slot());
            self::fail('SlotUnavailable attendue');
        } catch (SlotUnavailable $e) {
            self::assertSame(['hors horaires', 'déjà pris'], $e->reasons);
            self::assertSame('Créneau indisponible : hors horaires, déjà pris.', $e->getMessage());
        }
    }

    /** @return Specification<TimeSlot> */
    private static function rule(bool $result): Specification
    {
        /* @implements Specification<TimeSlot> */
        return new class($result) implements Specification {
            public function __construct(private readonly bool $result)
            {
            }

            public function isSatisfiedBy(mixed $candidate): bool
            {
                return $this->result;
            }
        };
    }

    private static function slot(): TimeSlot
    {
        return TimeSlot::startingAt(new \DateTimeImmutable('2026-01-05 09:00'), 30);
    }
}
