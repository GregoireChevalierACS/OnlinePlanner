<?php

declare(strict_types=1);

namespace App\Shared\Domain;

/**
 * L'action contredit l'état actuel (créneau pris, transition interdite) : la couche HTTP la traduit en 409.
 */
abstract class Conflict extends DomainException
{
    /**
     * @return list<string> motifs détaillés, affichables à l'utilisateur
     */
    public function reasons(): array
    {
        return [];
    }
}
