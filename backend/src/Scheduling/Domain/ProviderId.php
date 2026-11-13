<?php

declare(strict_types=1);

namespace App\Scheduling\Domain;

use App\Shared\Domain\Identifier;

/**
 * Le prestataire dont on gère l'agenda (praticien, salle, ressource…).
 */
final readonly class ProviderId extends Identifier
{
}
