<?php

declare(strict_types=1);

namespace App\Shared\Application;

/**
 * Port de génération d'identifiants : l'appelant crée l'identifiant avant d'envoyer la commande,
 * pour que la commande n'ait rien à renvoyer.
 */
interface IdGenerator
{
    public function next(): string;
}
