<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\DomainEventPublisher;
use App\Shared\Domain\DomainEvent;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * Observer via Messenger : les événements partent sur le bus d'événements
 * seulement après la fin (et donc le commit) de la commande en cours.
 */
#[AsAlias(DomainEventPublisher::class)]
final readonly class MessengerDomainEventPublisher implements DomainEventPublisher
{
    public function __construct(#[Autowire(service: 'event.bus')] private MessageBusInterface $bus)
    {
    }

    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->bus->dispatch(new Envelope($event, [new DispatchAfterCurrentBusStamp()]));
        }
    }
}
