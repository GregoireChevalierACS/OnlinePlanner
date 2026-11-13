<?php

declare(strict_types=1);

namespace App\Shared\Domain;

/**
 * Port vers l'horloge : rend le temps injectable et donc testable.
 */
interface Clock
{
    public function now(): \DateTimeImmutable;
}
