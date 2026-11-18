<?php

declare(strict_types=1);

namespace App\Shared\Application;

/**
 * Marqueur des abonnés aux Domain Events (Observer), branchés sur le bus d'événements par l'infrastructure.
 * Un événement peut avoir zéro, un ou plusieurs abonnés ; ils s'exécutent après le commit, de façon asynchrone.
 */
interface EventHandler
{
}
