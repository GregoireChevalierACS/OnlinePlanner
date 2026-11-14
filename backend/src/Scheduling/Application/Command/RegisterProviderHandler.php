<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Command;

use App\Scheduling\Domain\Availability\OpeningHours;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use App\Scheduling\Domain\Provider\Provider;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Scheduling\Domain\Provider\SlotSettings;
use App\Scheduling\Domain\ProviderId;
use App\Shared\Application\CommandHandler;

final readonly class RegisterProviderHandler implements CommandHandler
{
    public function __construct(private ProviderRepository $providers)
    {
    }

    public function __invoke(RegisterProvider $command): void
    {
        $schedule = new WeeklySchedule(...array_map(
            static fn (array $hours): OpeningHours => OpeningHours::on($hours['weekday'], $hours['opensAt'], $hours['closesAt']),
            $command->openingHours,
        ));

        $this->providers->save(Provider::register(
            new ProviderId($command->providerId),
            $command->name,
            $schedule,
            new SlotSettings($command->slotDurationMinutes, $command->bufferMinutes, $command->minimumNoticeMinutes),
        ));
    }
}
