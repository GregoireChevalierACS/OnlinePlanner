<?php

declare(strict_types=1);

namespace App\Tests\Contract\Identity;

use App\Identity\Infrastructure\Persistence\DoctrineAccountRepository;
use App\Identity\Infrastructure\Persistence\DoctrinePasswordResetRepository;
use App\Tests\ContainerAccess;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineAccountRepositoryTest extends KernelTestCase
{
    use AccountRepositoryContract;
    use ContainerAccess;
    use PasswordResetRepositoryContract;

    protected function repository(): DoctrineAccountRepository
    {
        return self::service(DoctrineAccountRepository::class);
    }

    protected function resets(): DoctrinePasswordResetRepository
    {
        return self::service(DoctrinePasswordResetRepository::class);
    }

    protected function clear(): void
    {
        self::service(EntityManagerInterface::class)->clear();
    }
}
