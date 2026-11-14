<?php

declare(strict_types=1);

namespace App\Shared\Application;

use App\Shared\Domain\DomainEvent;

/**
 * Port de publication des Domain Events (Observer) : les abonnés (notifications, journal…)
 * réagissent sans que le cas d'usage les connaisse.
 */
interface DomainEventPublisher
{
    public function publish(DomainEvent ...$events): void;
}
