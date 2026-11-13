<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment\Status;

final readonly class Cancelled extends BaseStatus
{
    public const string NAME = 'cancelled';

    public function name(): string
    {
        return self::NAME;
    }

    public function blocksSlot(): bool
    {
        return false;
    }
}
