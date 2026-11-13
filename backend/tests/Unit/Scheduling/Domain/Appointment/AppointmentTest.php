<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Appointment;

use App\Scheduling\Domain\Appointment\Appointment;
use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\Customer;
use App\Scheduling\Domain\Appointment\Email;
use App\Scheduling\Domain\Appointment\Event\AppointmentBooked;
use App\Scheduling\Domain\Appointment\Event\AppointmentCancelled;
use App\Scheduling\Domain\Appointment\Event\AppointmentConfirmed;
use App\Scheduling\Domain\Appointment\Status\Cancelled;
use App\Scheduling\Domain\Appointment\Status\Completed;
use App\Scheduling\Domain\Appointment\Status\Confirmed;
use App\Scheduling\Domain\Appointment\Status\Pending;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Booking\BookingPolicy;
use App\Scheduling\Domain\Booking\DoesNotOverlap;
use App\Scheduling\Domain\Exception\InvalidStatusTransition;
use App\Scheduling\Domain\Exception\SlotUnavailable;
use App\Scheduling\Domain\ProviderId;
use PHPUnit\Framework\TestCase;

final class AppointmentTest extends TestCase
{
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = new \DateTimeImmutable('2026-01-01 08:00');
    }

    public function testBookCreatesPendingAppointmentAndRecordsEvent(): void
    {
        $customer = new Customer('Jane', new Email('jane@example.com'));
        $slot = self::slot();

        $appointment = Appointment::book(new AppointmentId('a1'), new ProviderId('p1'), $customer, $slot, new BookingPolicy([]), $this->now);

        self::assertSame('a1', $appointment->id()->value);
        self::assertSame('p1', $appointment->providerId()->value);
        self::assertSame($customer, $appointment->customer());
        self::assertSame($slot, $appointment->slot());
        self::assertInstanceOf(Pending::class, $appointment->status());
        self::assertTrue($appointment->blocksSlot());

        $events = $appointment->pullDomainEvents();
        self::assertCount(1, $events);
        $event = $events[0];
        self::assertInstanceOf(AppointmentBooked::class, $event);
        self::assertSame('a1', $event->appointmentId->value);
        self::assertSame('p1', $event->providerId->value);
        self::assertSame($customer, $event->customer);
        self::assertSame($slot, $event->slot);
        self::assertSame($this->now, $event->occurredOn());
    }

    public function testBookRefusesSlotRejectedByPolicy(): void
    {
        $policy = new BookingPolicy(['déjà pris' => new DoesNotOverlap([self::slot()])]);

        $this->expectException(SlotUnavailable::class);

        Appointment::book(new AppointmentId('a1'), new ProviderId('p1'), new Customer('Jane', new Email('jane@example.com')), self::slot(), $policy, $this->now);
    }

    public function testConfirm(): void
    {
        $appointment = $this->bookedAppointment();

        $appointment->confirm($this->now);

        self::assertInstanceOf(Confirmed::class, $appointment->status());
        $events = $appointment->pullDomainEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(AppointmentConfirmed::class, $events[0]);
        self::assertSame('a1', $events[0]->appointmentId->value);
        self::assertSame($this->now, $events[0]->occurredOn());
    }

    public function testCancelFreesTheSlot(): void
    {
        $appointment = $this->bookedAppointment();

        $appointment->cancel('Empêchement', $this->now);

        self::assertInstanceOf(Cancelled::class, $appointment->status());
        self::assertFalse($appointment->blocksSlot());
        $events = $appointment->pullDomainEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(AppointmentCancelled::class, $events[0]);
        self::assertSame('a1', $events[0]->appointmentId->value);
        self::assertSame('Empêchement', $events[0]->reason);
        self::assertSame($this->now, $events[0]->occurredOn());
    }

    public function testCompleteOnceSlotHasEnded(): void
    {
        $appointment = $this->bookedAppointment();
        $appointment->confirm($this->now);

        $appointment->complete(self::slot()->end);

        self::assertInstanceOf(Completed::class, $appointment->status());
    }

    public function testCannotCompleteBeforeSlotEnds(): void
    {
        $appointment = $this->bookedAppointment();
        $appointment->confirm($this->now);

        $this->expectException(InvalidStatusTransition::class);
        $this->expectExceptionMessage('Un rendez-vous ne peut être terminé qu\'après son heure de fin.');

        $appointment->complete(self::slot()->end->modify('-1 second'));
    }

    public function testForbiddenTransitionIsDelegatedToStatus(): void
    {
        $appointment = $this->bookedAppointment();
        $appointment->cancel('x', $this->now);
        $appointment->pullDomainEvents();

        try {
            $appointment->confirm($this->now);
            self::fail('InvalidStatusTransition attendue');
        } catch (InvalidStatusTransition) {
            self::assertSame([], $appointment->pullDomainEvents());
        }
    }

    private function bookedAppointment(): Appointment
    {
        $appointment = Appointment::book(
            new AppointmentId('a1'),
            new ProviderId('p1'),
            new Customer('Jane', new Email('jane@example.com')),
            self::slot(),
            new BookingPolicy([]),
            $this->now,
        );
        $appointment->pullDomainEvents();

        return $appointment;
    }

    private static function slot(): TimeSlot
    {
        return TimeSlot::startingAt(new \DateTimeImmutable('2026-01-05 09:00'), 30);
    }
}
