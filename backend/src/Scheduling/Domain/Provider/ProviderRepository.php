<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Provider;

use App\Scheduling\Domain\Exception\ProviderNotFound;
use App\Scheduling\Domain\ProviderId;

/**
 * Port Repository : le domaine dit ce dont il a besoin, l'infrastructure fournit l'adapter.
 */
interface ProviderRepository
{
    public function save(Provider $provider): void;

    /**
     * @throws ProviderNotFound
     */
    public function get(ProviderId $id): Provider;

    /**
     * @return list<Provider> les agendas gérés par ce compte, triés par nom
     */
    public function ownedBy(OwnerId $ownerId): array;
}
