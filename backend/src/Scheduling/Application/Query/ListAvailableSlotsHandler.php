<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Query;

use App\Scheduling\Application\TakenSlots;
use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Domain\Availability\AvailabilityCalculator;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Scheduling\Domain\ProviderId;
use App\Shared\Application\QueryHandler;
use App\Shared\Domain\Clock;

final readonly class ListAvailableSlotsHandler implements QueryHandler
{
    public function __construct(
        private ProviderRepository $providers,
        private AppointmentRepository $appointments,
        private Clock $clock,
    ) {
    }

    /**
     * @return list<SlotView>
     */
    public function __invoke(ListAvailableSlots $query): array
    {
        $provider = $this->providers->get(new ProviderId($query->providerId));
        $midnight = $query->day->setTime(0, 0);
        $wholeDay = TimeSlot::between($midnight, $midnight->modify('+1 day'));
        $taken = TakenSlots::of($this->appointments->overlapping($provider->id(), $wholeDay));

        $slots = (new AvailabilityCalculator($provider->settings()->strategy()))
            ->availableSlots($provider->schedule(), $midnight, $provider->bookingPolicy($this->clock, $taken));

        return array_map(SlotView::fromSlot(...), $slots);
    }
}
