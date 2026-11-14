<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Exception;

use App\Scheduling\Domain\ProviderId;
use App\Shared\Domain\NotFound;

final class ProviderNotFound extends NotFound
{
    public static function withId(ProviderId $id): self
    {
        return new self(\sprintf('Prestataire « %s » introuvable.', $id));
    }
}
