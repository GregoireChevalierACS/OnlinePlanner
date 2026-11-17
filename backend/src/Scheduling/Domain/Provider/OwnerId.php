<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Provider;

use App\Shared\Domain\Identifier;

/**
 * Le compte qui gère cet agenda. Le contexte Scheduling ne connaît que son identifiant,
 * pas le modèle du contexte Identity.
 */
final readonly class OwnerId extends Identifier
{
}
