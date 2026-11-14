<?php

declare(strict_types=1);

namespace App\Tests\Contract\Scheduling;

use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Domain\Appointment\Status\Cancelled;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Exception\AppointmentNotFound;
use App\Scheduling\Domain\ProviderId;
use App\Tests\Fixture\AppointmentMother;
use PHPUnit\Framework\TestCase;

/**
 * Suite de contrat : chaque adapter de AppointmentRepository doit la passer à l'identique.
 */
abstract class AppointmentRepositoryContract extends TestCase
{
    abstract protected function repository(): AppointmentRepository;

    public function testSavedAppointmentCanBeRetrieved(): void
    {
        $repository = $this->repository();
        $repository->save(AppointmentMother::booked('a1', '2026-01-05 09:00'));

        $found = $repository->get(new AppointmentId('a1'));

        self::assertSame('a1', $found->id()->value);
        self::assertSame('p1', $found->providerId()->value);
        self::assertSame('Jane Doe', $found->customer()->name);
        self::assertSame('jane@example.com', $found->customer()->email->value);
        self::assertEquals(new \DateTimeImmutable('2026-01-05 09:00'), $found->slot()->start);
        self::assertEquals(new \DateTimeImmutable('2026-01-05 09:30'), $found->slot()->end);
        self::assertSame('pending', $found->status()->name());
    }

    public function testSavingAgainPersistsStatusChanges(): void
    {
        $repository = $this->repository();
        $appointment = AppointmentMother::booked('a1', '2026-01-05 09:00');
        $repository->save($appointment);

        $appointment->cancel('Empêchement', new \DateTimeImmutable('2026-01-02'));
        $repository->save($appointment);

        self::assertInstanceOf(Cancelled::class, $repository->get(new AppointmentId('a1'))->status());
    }

    public function testUnknownAppointmentThrows(): void
    {
        $this->expectException(AppointmentNotFound::class);
        $this->expectExceptionMessage('Rendez-vous « inconnu » introuvable.');

        $this->repository()->get(new AppointmentId('inconnu'));
    }

    public function testOverlappingReturnsProviderAppointmentsInPeriodSortedByStart(): void
    {
        $repository = $this->repository();
        $repository->save(AppointmentMother::booked('late', '2026-01-05 11:00'));
        $repository->save(AppointmentMother::booked('early', '2026-01-05 09:00'));
        $repository->save(AppointmentMother::booked('before', '2026-01-05 08:15'));
        $repository->save(AppointmentMother::booked('next-day', '2026-01-06 09:00'));
        $repository->save(AppointmentMother::booked('other', '2026-01-05 10:00', 'p2'));
        $cancelled = AppointmentMother::booked('cancelled', '2026-01-05 10:00');
        $cancelled->cancel('x', new \DateTimeImmutable('2026-01-02'));
        $repository->save($cancelled);

        $period = TimeSlot::between(new \DateTimeImmutable('2026-01-05 08:30'), new \DateTimeImmutable('2026-01-06 00:00'));
        $ids = array_map(
            static fn ($a): string => $a->id()->value,
            $repository->overlapping(new ProviderId('p1'), $period),
        );

        self::assertSame(['before', 'early', 'cancelled', 'late'], $ids);
    }
}
