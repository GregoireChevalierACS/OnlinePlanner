<?php

declare(strict_types=1);

namespace App\Tests\Contract\Scheduling;

use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryAppointmentRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryAppointmentRepositoryTest extends TestCase
{
    use AppointmentRepositoryContract;

    private InMemoryAppointmentRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAppointmentRepository();
    }

    protected function repository(): AppointmentRepository
    {
        return $this->repository;
    }

    protected function clear(): void
    {
    }
}
