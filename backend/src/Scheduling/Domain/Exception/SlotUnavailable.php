<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Exception;

use App\Shared\Domain\DomainException;

final class SlotUnavailable extends DomainException
{
    /**
     * @param non-empty-list<string> $reasons
     */
    private function __construct(public readonly array $reasons)
    {
        parent::__construct('Créneau indisponible : '.implode(', ', $reasons).'.');
    }

    /**
     * @param non-empty-list<string> $reasons
     */
    public static function because(array $reasons): self
    {
        return new self($reasons);
    }
}
