<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Persistence\InMemory;

use App\Scheduling\Domain\Exception\ProviderNotFound;
use App\Scheduling\Domain\Provider\Provider;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Scheduling\Domain\ProviderId;

/**
 * Adapter en mémoire : tests et démonstrations, soumis à la même suite de contrat que Doctrine.
 */
final class InMemoryProviderRepository implements ProviderRepository
{
    /** @var array<string, Provider> */
    private array $providers = [];

    public function save(Provider $provider): void
    {
        $this->providers[$provider->id()->value] = clone $provider;
    }

    public function get(ProviderId $id): Provider
    {
        return clone ($this->providers[$id->value] ?? throw ProviderNotFound::withId($id));
    }
}
