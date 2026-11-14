<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Shared\Application\DomainEventPublisher;
use App\Shared\Domain\DomainEvent;

final class RecordingEventPublisher implements DomainEventPublisher
{
    /** @var list<DomainEvent> */
    public array $published = [];

    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->published[] = $event;
        }
    }
}
