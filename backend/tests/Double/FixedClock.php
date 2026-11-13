<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Shared\Domain\Clock;

final readonly class FixedClock implements Clock
{
    public function __construct(private \DateTimeImmutable $now)
    {
    }

    public static function at(string $datetime): self
    {
        return new self(new \DateTimeImmutable($datetime));
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}
