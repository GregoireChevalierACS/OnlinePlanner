<?php

declare(strict_types=1);

namespace App\Tests\Contract\Scheduling;

use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryAppointmentRepository;

final class InMemoryAppointmentRepositoryTest extends AppointmentRepositoryContract
{
    protected function repository(): AppointmentRepository
    {
        return new InMemoryAppointmentRepository();
    }
}
