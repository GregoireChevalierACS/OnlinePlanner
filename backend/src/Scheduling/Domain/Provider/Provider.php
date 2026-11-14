<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Provider;

use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use App\Scheduling\Domain\Booking\AlignedWithSlotGrid;
use App\Scheduling\Domain\Booking\BookingPolicy;
use App\Scheduling\Domain\Booking\DoesNotOverlap;
use App\Scheduling\Domain\Booking\RespectsMinimumNotice;
use App\Scheduling\Domain\Booking\WithinOpeningHours;
use App\Scheduling\Domain\Exception\InvalidProvider;
use App\Scheduling\Domain\ProviderId;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\Clock;

/**
 * Aggregate Root : le prestataire, ses horaires et ses réglages de réservation.
 */
final class Provider extends AggregateRoot
{
    public const string RULE_OPENING_HOURS = 'en dehors des horaires d\'ouverture';
    public const string RULE_SLOT_GRID = 'ne correspond pas à un créneau proposé';
    public const string RULE_ALREADY_TAKEN = 'déjà réservé';
    public const string RULE_MINIMUM_NOTICE = 'délai de prévenance non respecté';

    private function __construct(
        private readonly ProviderId $id,
        private readonly string $name,
        private readonly WeeklySchedule $schedule,
        private readonly SlotSettings $settings,
    ) {
    }

    public static function register(ProviderId $id, string $name, WeeklySchedule $schedule, SlotSettings $settings): self
    {
        $trimmed = trim($name);
        if ('' === $trimmed) {
            throw InvalidProvider::emptyName();
        }

        return new self($id, $trimmed, $schedule, $settings);
    }

    /**
     * Factory : la politique de réservation de ce prestataire, à un instant donné.
     *
     * @param list<TimeSlot> $takenSlots créneaux déjà occupés
     */
    public function bookingPolicy(Clock $clock, array $takenSlots): BookingPolicy
    {
        return new BookingPolicy([
            self::RULE_OPENING_HOURS => new WithinOpeningHours($this->schedule),
            self::RULE_SLOT_GRID => new AlignedWithSlotGrid($this->schedule, $this->settings->strategy()),
            self::RULE_ALREADY_TAKEN => new DoesNotOverlap($takenSlots),
            self::RULE_MINIMUM_NOTICE => new RespectsMinimumNotice($clock, $this->settings->minimumNoticeMinutes),
        ]);
    }

    public function slotStartingAt(\DateTimeImmutable $start): TimeSlot
    {
        return TimeSlot::startingAt($start, $this->settings->durationMinutes);
    }

    public function id(): ProviderId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function schedule(): WeeklySchedule
    {
        return $this->schedule;
    }

    public function settings(): SlotSettings
    {
        return $this->settings;
    }
}
