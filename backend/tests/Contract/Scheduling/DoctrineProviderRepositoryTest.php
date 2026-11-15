<?php

declare(strict_types=1);

namespace App\Tests\Contract\Scheduling;

use App\Scheduling\Infrastructure\Persistence\Doctrine\DoctrineProviderRepository;
use App\Tests\ContainerAccess;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineProviderRepositoryTest extends KernelTestCase
{
    use ContainerAccess;
    use ProviderRepositoryContract;

    protected function repository(): DoctrineProviderRepository
    {
        return self::service(DoctrineProviderRepository::class);
    }

    protected function clear(): void
    {
        self::service(EntityManagerInterface::class)->clear();
    }
}
