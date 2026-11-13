<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment\Status;

final readonly class Completed extends BaseStatus
{
    public const string NAME = 'completed';

    public function name(): string
    {
        return self::NAME;
    }
}
