<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Command;

use App\Shared\Application\Command;

final readonly class RegisterProvider implements Command
{
    /**
     * @param list<array{weekday: int, opensAt: string, closesAt: string}> $openingHours
     */
    public function __construct(
        public string $providerId,
        public string $name,
        public array $openingHours,
        public int $slotDurationMinutes,
        public int $bufferMinutes = 0,
        public int $minimumNoticeMinutes = 0,
    ) {
    }
}
