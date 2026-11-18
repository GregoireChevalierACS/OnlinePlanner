<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Notification;

use App\Scheduling\Domain\Provider\OwnerId;

/**
 * Port : adresse de contact du propriétaire d'un agenda.
 * Scheduling ne connaît pas le modèle des comptes : l'adapter interroge le contexte Identity.
 */
interface OwnerDirectory
{
    public function emailOf(OwnerId $ownerId): ?string;
}
