<?php

declare(strict_types=1);

namespace App\Shared\Domain;

/**
 * Domain Event : fait métier passé, immuable, publié après persistance (Observer).
 */
interface DomainEvent
{
    public function occurredOn(): \DateTimeImmutable;
}
