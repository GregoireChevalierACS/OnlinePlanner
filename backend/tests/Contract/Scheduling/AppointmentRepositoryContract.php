<?php

declare(strict_types=1);

namespace App\Tests\Contract\Scheduling;

use App\Scheduling\Domain\Appointment\Appointment;
use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Domain\Appointment\Status\Cancelled;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Exception\AppointmentNotFound;
use App\Scheduling\Domain\Exception\SlotUnavailable;
use App\Scheduling\Domain\Provider\Provider;
use App\Scheduling\Domain\ProviderId;
use App\Tests\Fixture\AppointmentMother;

/**
 * Suite de contrat : chaque adapter de AppointmentRepository doit la passer à l'identique.
 */
trait AppointmentRepositoryContract
{
    abstract protected function repository(): AppointmentRepository;

    /** Oublie les objets déjà chargés, pour relire réellement depuis le stockage. */
    abstract protected function clear(): void;

    public function testSavedAppointmentCanBeRetrieved(): void
    {
        $repository = $this->repository();
        $repository->save(AppointmentMother::booked('a1', '2026-01-05 09:00'));
        $this->clear();

        $found = $repository->get(new AppointmentId('a1'));

        self::assertSame('a1', $found->id()->value);
        self::assertSame('p1', $found->providerId()->value);
        self::assertSame('Jane Doe', $found->customer()->name);
        self::assertSame('jane@example.com', $found->customer()->email->value);
        self::assertSame('2026-01-05 09:00', $found->slot()->start->format('Y-m-d H:i'));
        self::assertSame('2026-01-05 09:30', $found->slot()->end->format('Y-m-d H:i'));
        self::assertSame('pending', $found->status()->name());
        self::assertSame([], $found->pullDomainEvents());
    }

    public function testSavingAgainPersistsStatusChanges(): void
    {
        $repository = $this->repository();
        $appointment = AppointmentMother::booked('a1', '2026-01-05 09:00');
        $repository->save($appointment);

        $appointment->cancel('Empêchement', new \DateTimeImmutable('2026-01-02'));
        $repository->save($appointment);
        $this->clear();

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
        $this->clear();

        $period = TimeSlot::between(new \DateTimeImmutable('2026-01-05 08:30'), new \DateTimeImmutable('2026-01-06 00:00'));
        $ids = array_map(
            static fn (Appointment $a): string => $a->id()->value,
            $repository->overlapping(new ProviderId('p1'), $period),
        );

        self::assertSame(['before', 'early', 'cancelled', 'late'], $ids);
    }

    public function testRefusesTwoActiveOverlappingAppointmentsForSameProvider(): void
    {
        $repository = $this->repository();
        $repository->save(AppointmentMother::booked('first', '2026-01-05 09:00'));

        try {
            $repository->save(AppointmentMother::booked('second', '2026-01-05 09:15'));
            self::fail('SlotUnavailable attendue');
        } catch (SlotUnavailable $e) {
            self::assertSame([Provider::RULE_ALREADY_TAKEN], $e->reasons);
        }
    }

    public function testCancelledAppointmentsAndOtherProvidersDoNotConflict(): void
    {
        $repository = $this->repository();
        $cancelled = AppointmentMother::booked('cancelled', '2026-01-05 09:00');
        $cancelled->cancel('x', new \DateTimeImmutable('2026-01-02'));
        $repository->save($cancelled);
        $repository->save(AppointmentMother::booked('other-provider', '2026-01-05 09:00', 'p2'));
        $repository->save(AppointmentMother::booked('replacement', '2026-01-05 09:00'));
        $repository->save(AppointmentMother::booked('contiguous', '2026-01-05 09:30'));
        $this->clear();

        $period = TimeSlot::between(new \DateTimeImmutable('2026-01-05 00:00'), new \DateTimeImmutable('2026-01-06 00:00'));
        self::assertCount(3, $repository->overlapping(new ProviderId('p1'), $period));
    }
}
