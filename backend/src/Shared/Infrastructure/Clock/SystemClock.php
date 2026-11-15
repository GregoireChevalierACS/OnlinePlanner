<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Clock;

use App\Shared\Domain\Clock;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Adapter du port Clock sur le composant Symfony Clock (remplaçable par MockClock en test).
 */
#[AsAlias(Clock::class)]
final readonly class SystemClock implements Clock
{
    public function __construct(private ClockInterface $clock)
    {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->clock->now();
    }
}
