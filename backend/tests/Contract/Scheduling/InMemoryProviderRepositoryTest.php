<?php

declare(strict_types=1);

namespace App\Tests\Contract\Scheduling;

use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryProviderRepository;

final class InMemoryProviderRepositoryTest extends ProviderRepositoryContract
{
    protected function repository(): ProviderRepository
    {
        return new InMemoryProviderRepository();
    }
}
