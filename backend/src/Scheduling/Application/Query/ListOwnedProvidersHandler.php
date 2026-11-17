<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Query;

use App\Scheduling\Domain\Provider\OwnerId;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Shared\Application\QueryHandler;

final readonly class ListOwnedProvidersHandler implements QueryHandler
{
    public function __construct(private ProviderRepository $providers)
    {
    }

    /**
     * @return list<ProviderView>
     */
    public function __invoke(ListOwnedProviders $query): array
    {
        return array_map(ProviderView::fromProvider(...), $this->providers->ownedBy(new OwnerId($query->ownerId)));
    }
}
