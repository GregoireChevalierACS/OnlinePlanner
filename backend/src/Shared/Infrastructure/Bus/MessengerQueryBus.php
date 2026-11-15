<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Query;
use App\Shared\Application\QueryBus;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[AsAlias(QueryBus::class)]
final readonly class MessengerQueryBus implements QueryBus
{
    public function __construct(#[Autowire(service: 'query.bus')] private MessageBusInterface $bus)
    {
    }

    public function ask(Query $query): mixed
    {
        try {
            $envelope = $this->bus->dispatch($query);
        } catch (HandlerFailedException $e) {
            throw UnwrapHandlerFailure::of($e);
        }

        return $envelope->last(HandledStamp::class)?->getResult();
    }
}
