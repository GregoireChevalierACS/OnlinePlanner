<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Application;

use App\Scheduling\Application\Command\CancelAppointment;
use App\Scheduling\Application\Command\CancelAppointmentHandler;
use App\Scheduling\Application\Command\ConfirmAppointment;
use App\Scheduling\Application\Command\ConfirmAppointmentHandler;
use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\Event\AppointmentCancelled;
use App\Scheduling\Domain\Appointment\Event\AppointmentConfirmed;
use App\Scheduling\Domain\Appointment\Status\Cancelled;
use App\Scheduling\Domain\Appointment\Status\Confirmed;
use App\Scheduling\Domain\Exception\AppointmentNotFound;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryAppointmentRepository;
use App\Tests\Double\FixedClock;
use App\Tests\Double\RecordingEventPublisher;
use App\Tests\Fixture\AppointmentMother;
use PHPUnit\Framework\TestCase;

final class AppointmentLifecycleHandlersTest extends TestCase
{
    private InMemoryAppointmentRepository $appointments;
    private RecordingEventPublisher $events;
    private FixedClock $clock;

    protected function setUp(): void
    {
        $this->appointments = new InMemoryAppointmentRepository();
        $this->appointments->save(AppointmentMother::booked('a1', '2026-01-05 09:00'));
        $this->events = new RecordingEventPublisher();
        $this->clock = FixedClock::at('2026-01-03 12:00');
    }

    public function testConfirm(): void
    {
        (new ConfirmAppointmentHandler($this->appointments, $this->clock, $this->events))(new ConfirmAppointment('a1'));

        self::assertInstanceOf(Confirmed::class, $this->appointments->get(new AppointmentId('a1'))->status());
        self::assertCount(1, $this->events->published);
        self::assertInstanceOf(AppointmentConfirmed::class, $this->events->published[0]);
        self::assertEquals($this->clock->now(), $this->events->published[0]->occurredOn());
    }

    public function testCancel(): void
    {
        (new CancelAppointmentHandler($this->appointments, $this->clock, $this->events))(new CancelAppointment('a1', 'Empêchement'));

        self::assertInstanceOf(Cancelled::class, $this->appointments->get(new AppointmentId('a1'))->status());
        self::assertCount(1, $this->events->published);
        $event = $this->events->published[0];
        self::assertInstanceOf(AppointmentCancelled::class, $event);
        self::assertSame('Empêchement', $event->reason);
        self::assertEquals($this->clock->now(), $event->occurredOn());
    }

    public function testConfirmUnknownAppointment(): void
    {
        $this->expectException(AppointmentNotFound::class);

        (new ConfirmAppointmentHandler($this->appointments, $this->clock, $this->events))(new ConfirmAppointment('inconnu'));
    }

    public function testCancelUnknownAppointment(): void
    {
        $this->expectException(AppointmentNotFound::class);

        (new CancelAppointmentHandler($this->appointments, $this->clock, $this->events))(new CancelAppointment('inconnu', 'x'));
    }
}
