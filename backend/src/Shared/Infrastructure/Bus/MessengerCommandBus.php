<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Command;
use App\Shared\Application\CommandBus;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Adapter du port CommandBus sur Symfony Messenger (bus synchrone et transactionnel).
 */
#[AsAlias(CommandBus::class)]
final readonly class MessengerCommandBus implements CommandBus
{
    public function __construct(#[Autowire(service: 'command.bus')] private MessageBusInterface $bus)
    {
    }

    public function dispatch(Command $command): void
    {
        try {
            $this->bus->dispatch($command);
        } catch (HandlerFailedException $e) {
            throw UnwrapHandlerFailure::of($e);
        }
    }
}
