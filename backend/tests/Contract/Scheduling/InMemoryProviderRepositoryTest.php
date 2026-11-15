<?php

declare(strict_types=1);

namespace App\Tests\Contract\Scheduling;

use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryProviderRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryProviderRepositoryTest extends TestCase
{
    use ProviderRepositoryContract;

    private InMemoryProviderRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryProviderRepository();
    }

    protected function repository(): ProviderRepository
    {
        return $this->repository;
    }

    protected function clear(): void
    {
    }
}
