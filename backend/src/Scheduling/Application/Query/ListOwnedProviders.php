<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Query;

use App\Shared\Application\Query;

/**
 * Les agendas gérés par un compte.
 */
final readonly class ListOwnedProviders implements Query
{
    public function __construct(public string $ownerId)
    {
    }
}
