<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Exception;

use App\Shared\Domain\DomainException;

final class InvalidStatusTransition extends DomainException
{
    public static function from(string $status, string $action): self
    {
        return new self(\sprintf('Action « %s » impossible sur un rendez-vous au statut « %s ».', $action, $status));
    }

    public static function unknownStatus(string $status): self
    {
        return new self(\sprintf('Statut de rendez-vous inconnu : « %s ».', $status));
    }

    public static function notFinishedYet(): self
    {
        return new self('Un rendez-vous ne peut être terminé qu\'après son heure de fin.');
    }
}
