<?php

declare(strict_types=1);

namespace App\Shared\Domain;

/**
 * Aggregate Root : frontière de cohérence qui enregistre ses événements métier
 * jusqu'à ce que la couche application les publie.
 */
abstract class AggregateRoot
{
    /** @var list<DomainEvent> */
    private array $domainEvents = [];

    protected function recordThat(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @return list<DomainEvent>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
