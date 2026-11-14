<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Provider;

use App\Scheduling\Domain\Availability\FixedIntervalStrategy;
use App\Scheduling\Domain\Availability\OpeningHours;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use App\Scheduling\Domain\Exception\InvalidProvider;
use App\Scheduling\Domain\Exception\InvalidSlotStrategy;
use App\Scheduling\Domain\Provider\Provider;
use App\Scheduling\Domain\Provider\SlotSettings;
use App\Scheduling\Domain\ProviderId;
use App\Tests\Double\FixedClock;
use App\Tests\Fixture\ProviderMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProviderTest extends TestCase
{
    public function testRegisterTrimsNameAndKeepsSettings(): void
    {
        $schedule = new WeeklySchedule(OpeningHours::on(1, '09:00', '12:00'));
        $settings = new SlotSettings(30);

        $provider = Provider::register(new ProviderId('p1'), '  Dr Martin ', $schedule, $settings);

        self::assertSame('p1', $provider->id()->value);
        self::assertSame('Dr Martin', $provider->name());
        self::assertSame($schedule, $provider->schedule());
        self::assertSame($settings, $provider->settings());
    }

    public function testRegisterRejectsBlankName(): void
    {
        $this->expectException(InvalidProvider::class);
        $this->expectExceptionMessage('Le nom du prestataire est obligatoire.');

        Provider::register(new ProviderId('p1'), ' ', new WeeklySchedule(), new SlotSettings(30));
    }

    public function testSlotStartingAtUsesConfiguredDuration(): void
    {
        $slot = ProviderMother::doctor()->slotStartingAt(new \DateTimeImmutable('2026-01-05 09:00'));

        self::assertEquals(new \DateTimeImmutable('2026-01-05 09:30'), $slot->end);
    }

    /**
     * @param list<string> $expectedViolations
     */
    #[DataProvider('policyCases')]
    public function testBookingPolicyAppliesEveryRule(string $start, array $expectedViolations): void
    {
        $provider = ProviderMother::doctor('p1', 60);
        $taken = [TimeSlot::startingAt(new \DateTimeImmutable('2026-01-05 10:00'), 30)];

        $policy = $provider->bookingPolicy(FixedClock::at('2026-01-05 08:30'), $taken);

        self::assertSame($expectedViolations, $policy->violations($provider->slotStartingAt(new \DateTimeImmutable('2026-01-05 '.$start))));
    }

    /** @return iterable<string, array{string, list<string>}> */
    public static function policyCases(): iterable
    {
        yield 'réservable' => ['09:30', []];
        yield 'hors horaires' => ['12:00', [Provider::RULE_OPENING_HOURS, Provider::RULE_SLOT_GRID]];
        yield 'hors grille' => ['10:45', [Provider::RULE_SLOT_GRID]];
        yield 'déjà pris' => ['10:00', [Provider::RULE_ALREADY_TAKEN]];
        yield 'trop tôt' => ['09:00', [Provider::RULE_MINIMUM_NOTICE]];
    }

    public function testSettingsDefaultsAndStrategy(): void
    {
        $settings = new SlotSettings(45);

        self::assertSame(0, $settings->bufferMinutes);
        self::assertSame(0, $settings->minimumNoticeMinutes);
        self::assertEquals(new FixedIntervalStrategy(45, 0), $settings->strategy());
        self::assertEquals(new FixedIntervalStrategy(20, 5), (new SlotSettings(20, 5, 0))->strategy());
    }

    public function testSettingsRejectNegativeNotice(): void
    {
        $this->expectException(InvalidProvider::class);
        $this->expectExceptionMessage('négatif, -1 reçu');

        new SlotSettings(30, 0, -1);
    }

    public function testSettingsDelegateDurationValidationToStrategy(): void
    {
        $this->expectException(InvalidSlotStrategy::class);

        new SlotSettings(0);
    }
}
