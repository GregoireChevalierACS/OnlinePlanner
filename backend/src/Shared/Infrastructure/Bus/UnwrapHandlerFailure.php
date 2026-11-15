<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * Messenger enveloppe les exceptions des handlers : on rend l'erreur d'origine
 * (métier ou technique) pour que l'appelant n'ait pas à connaître Messenger.
 */
final class UnwrapHandlerFailure
{
    public static function of(HandlerFailedException $failure): \Throwable
    {
        return $failure->getWrappedExceptions()[0] ?? $failure->getPrevious() ?? $failure;
    }
}
