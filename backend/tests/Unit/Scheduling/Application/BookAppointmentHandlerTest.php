<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Application;

use App\Scheduling\Application\Command\BookAppointment;
use App\Scheduling\Application\Command\BookAppointmentHandler;
use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\Event\AppointmentBooked;
use App\Scheduling\Domain\Exception\ProviderNotFound;
use App\Scheduling\Domain\Exception\SlotUnavailable;
use App\Scheduling\Domain\Provider\Provider;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryAppointmentRepository;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryProviderRepository;
use App\Tests\Double\FixedClock;
use App\Tests\Double\RecordingEventPublisher;
use App\Tests\Fixture\AppointmentMother;
use App\Tests\Fixture\ProviderMother;
use PHPUnit\Framework\TestCase;

final class BookAppointmentHandlerTest extends TestCase
{
    private InMemoryAppointmentRepository $appointments;
    private RecordingEventPublisher $events;
    private BookAppointmentHandler $handler;

    protected function setUp(): void
    {
        $providers = new InMemoryProviderRepository();
        $providers->save(ProviderMother::doctor());
        $this->appointments = new InMemoryAppointmentRepository();
        $this->events = new RecordingEventPublisher();
        $this->handler = new BookAppointmentHandler($providers, $this->appointments, FixedClock::at('2026-01-05 07:00'), $this->events);
    }

    public function testBooksFreeSlotAndPublishesEvent(): void
    {
        ($this->handler)(self::command('a1', '09:30'));

        $saved = $this->appointments->get(new AppointmentId('a1'));
        self::assertSame('p1', $saved->providerId()->value);
        self::assertSame('Jane Doe', $saved->customer()->name);
        self::assertSame('jane@example.com', $saved->customer()->email->value);
        self::assertEquals(new \DateTimeImmutable('2026-01-05 10:00'), $saved->slot()->end);
        self::assertCount(1, $this->events->published);
        self::assertInstanceOf(AppointmentBooked::class, $this->events->published[0]);
        self::assertEquals(new \DateTimeImmutable('2026-01-05 07:00'), $this->events->published[0]->occurredOn());
        self::assertSame([], $saved->pullDomainEvents());
    }

    public function testRefusesSlotAlreadyTaken(): void
    {
        $this->appointments->save(AppointmentMother::booked('existing', '2026-01-05 09:30'));

        try {
            ($this->handler)(self::command('a1', '09:30'));
            self::fail('SlotUnavailable attendue');
        } catch (SlotUnavailable $e) {
            self::assertSame([Provider::RULE_ALREADY_TAKEN], $e->reasons);
        }
        self::assertSame([], $this->events->published);
    }

    public function testCancelledAppointmentFreesTheSlot(): void
    {
        $cancelled = AppointmentMother::booked('old', '2026-01-05 09:30');
        $cancelled->cancel('x', new \DateTimeImmutable('2026-01-02'));
        $this->appointments->save($cancelled);

        ($this->handler)(self::command('a1', '09:30'));

        self::assertSame('a1', $this->appointments->get(new AppointmentId('a1'))->id()->value);
    }

    public function testUnknownProvider(): void
    {
        $this->expectException(ProviderNotFound::class);

        ($this->handler)(new BookAppointment('a1', 'inconnu', new \DateTimeImmutable('2026-01-05 09:30'), 'Jane', 'jane@example.com'));
    }

    private static function command(string $id, string $time): BookAppointment
    {
        return new BookAppointment($id, 'p1', new \DateTimeImmutable(ProviderMother::MONDAY.' '.$time), 'Jane Doe', 'Jane@Example.com');
    }
}
