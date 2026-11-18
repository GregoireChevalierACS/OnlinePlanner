<?php

declare(strict_types=1);

namespace App\Tests\Contract\Identity;

use App\Identity\Infrastructure\Persistence\InMemoryAccountRepository;
use App\Identity\Infrastructure\Persistence\InMemoryPasswordResetRepository;
use PHPUnit\Framework\TestCase;

/**
 * Adapters en mémoire du contexte Identity, soumis aux mêmes suites de contrat que Doctrine.
 */
final class InMemoryAccountRepositoryTest extends TestCase
{
    use AccountRepositoryContract;
    use PasswordResetRepositoryContract;

    private InMemoryAccountRepository $repository;
    private InMemoryPasswordResetRepository $resets;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAccountRepository();
        $this->resets = new InMemoryPasswordResetRepository();
    }

    protected function repository(): InMemoryAccountRepository
    {
        return $this->repository;
    }

    protected function resets(): InMemoryPasswordResetRepository
    {
        return $this->resets;
    }

    protected function clear(): void
    {
    }
}
