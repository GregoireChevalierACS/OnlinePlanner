<?php

declare(strict_types=1);

namespace App\Tests\Contract\Identity;

use App\Identity\Infrastructure\Persistence\InMemoryAccountRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryAccountRepositoryTest extends TestCase
{
    use AccountRepositoryContract;

    private InMemoryAccountRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAccountRepository();
    }

    protected function repository(): InMemoryAccountRepository
    {
        return $this->repository;
    }

    protected function clear(): void
    {
    }
}
