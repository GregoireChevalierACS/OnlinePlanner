<?php

declare(strict_types=1);

namespace App\Tests\Contract\Scheduling;

use App\Scheduling\Infrastructure\Persistence\Doctrine\DoctrineAppointmentRepository;
use App\Tests\ContainerAccess;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Même suite de contrat, sur PostgreSQL réel (chaque test est annulé par DAMA DoctrineTestBundle).
 */
final class DoctrineAppointmentRepositoryTest extends KernelTestCase
{
    use AppointmentRepositoryContract;
    use ContainerAccess;

    protected function repository(): DoctrineAppointmentRepository
    {
        return self::service(DoctrineAppointmentRepository::class);
    }

    protected function clear(): void
    {
        self::service(EntityManagerInterface::class)->clear();
    }
}
