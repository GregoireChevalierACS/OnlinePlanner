<?php

declare(strict_types=1);

namespace App\Shared\Application;

/**
 * Port du bus de commandes (Command pattern + Mediator) : l'appelant ne connaît pas le handler.
 */
interface CommandBus
{
    public function dispatch(Command $command): void;
}
