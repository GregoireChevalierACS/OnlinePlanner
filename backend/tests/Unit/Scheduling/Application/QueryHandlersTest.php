<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Application;

use App\Scheduling\Application\Query\AppointmentView;
use App\Scheduling\Application\Query\GetAppointment;
use App\Scheduling\Application\Query\GetAppointmentHandler;
use App\Scheduling\Application\Query\ListAppointments;
use App\Scheduling\Application\Query\ListAppointmentsHandler;
use App\Scheduling\Application\Query\ListAvailableSlots;
use App\Scheduling\Application\Query\ListAvailableSlotsHandler;
use App\Scheduling\Application\Query\SlotView;
use App\Scheduling\Domain\Exception\AppointmentNotFound;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryAppointmentRepository;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryProviderRepository;
use App\Tests\Double\FixedClock;
use App\Tests\Fixture\AppointmentMother;
use App\Tests\Fixture\ProviderMother;
use PHPUnit\Framework\TestCase;

final class QueryHandlersTest extends TestCase
{
    private InMemoryProviderRepository $providers;
    private InMemoryAppointmentRepository $appointments;

    protected function setUp(): void
    {
        $this->providers = new InMemoryProviderRepository();
        $this->providers->save(ProviderMother::doctor('p1', 60));
        $this->appointments = new InMemoryAppointmentRepository();
    }

    public function testAvailableSlotsExcludeTakenTooSoonAndCancelledFreesSlot(): void
    {
        $this->appointments->save(AppointmentMother::booked('taken', '2026-01-05 10:00'));
        $cancelled = AppointmentMother::booked('cancelled', '2026-01-05 11:00');
        $cancelled->cancel('x', new \DateTimeImmutable('2026-01-02'));
        $this->appointments->save($cancelled);
        $this->appointments->save(AppointmentMother::booked('previous-evening', '2026-01-04 23:45', 'p1', 30));

        $handler = new ListAvailableSlotsHandler($this->providers, $this->appointments, FixedClock::at('2026-01-05 08:30'));
        $slots = $handler(new ListAvailableSlots('p1', new \DateTimeImmutable('2026-01-05 15:42')));

        self::assertSame(
            ['09:30', '10:30', '11:00', '11:30'],
            array_map(static fn (SlotView $s): string => substr($s->startsAt, 11, 5), $slots),
        );
        self::assertSame((new \DateTimeImmutable('2026-01-05 09:30'))->format(\DATE_ATOM), $slots[0]->startsAt);
        self::assertSame((new \DateTimeImmutable('2026-01-05 10:00'))->format(\DATE_ATOM), $slots[0]->endsAt);
    }

    public function testGetAppointment(): void
    {
        $this->appointments->save(AppointmentMother::booked('a1', '2026-01-05 09:00'));

        $view = (new GetAppointmentHandler($this->appointments))(new GetAppointment('a1'));

        self::assertEquals(new AppointmentView(
            'a1',
            'p1',
            'Jane Doe',
            'jane@example.com',
            (new \DateTimeImmutable('2026-01-05 09:00'))->format(\DATE_ATOM),
            (new \DateTimeImmutable('2026-01-05 09:30'))->format(\DATE_ATOM),
            'pending',
        ), $view);
    }

    public function testGetUnknownAppointment(): void
    {
        $this->expectException(AppointmentNotFound::class);

        (new GetAppointmentHandler($this->appointments))(new GetAppointment('inconnu'));
    }

    public function testListAppointmentsCoversWholeDaysInclusive(): void
    {
        $this->appointments->save(AppointmentMother::booked('before', '2026-01-04 23:30'));
        $this->appointments->save(AppointmentMother::booked('first', '2026-01-04 23:59', 'p1', 2));
        $this->appointments->save(AppointmentMother::booked('last', '2026-01-06 23:59', 'p1', 1));
        $this->appointments->save(AppointmentMother::booked('after', '2026-01-07 00:00'));

        $views = (new ListAppointmentsHandler($this->appointments))(new ListAppointments(
            'p1',
            new \DateTimeImmutable('2026-01-05 18:00'),
            new \DateTimeImmutable('2026-01-06 08:00'),
        ));

        self::assertSame(['first', 'last'], array_map(static fn (AppointmentView $v): string => $v->id, $views));
    }
}
