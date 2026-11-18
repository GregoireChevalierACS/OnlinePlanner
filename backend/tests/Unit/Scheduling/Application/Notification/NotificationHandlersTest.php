<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Application\Notification;

use App\Scheduling\Application\Notification\NotifyOnAppointmentBooked;
use App\Scheduling\Application\Notification\NotifyOnAppointmentCancelled;
use App\Scheduling\Application\Notification\NotifyOnAppointmentConfirmed;
use App\Scheduling\Application\Notification\OwnerDirectory;
use App\Scheduling\Domain\Appointment\Event\AppointmentBooked;
use App\Scheduling\Domain\Appointment\Event\AppointmentCancelled;
use App\Scheduling\Domain\Appointment\Event\AppointmentConfirmed;
use App\Scheduling\Domain\Provider\OwnerId;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryAppointmentRepository;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryProviderRepository;
use App\Tests\Double\RecordingNotifier;
use App\Tests\Fixture\AppointmentMother;
use App\Tests\Fixture\ProviderMother;
use PHPUnit\Framework\TestCase;

final class NotificationHandlersTest extends TestCase
{
    private InMemoryProviderRepository $providers;
    private InMemoryAppointmentRepository $appointments;
    private RecordingNotifier $notifier;

    protected function setUp(): void
    {
        $this->providers = new InMemoryProviderRepository();
        $this->providers->save(ProviderMother::doctor('p1', ownerId: 'owner-1', name: 'Cabinet Martin'));
        $this->appointments = new InMemoryAppointmentRepository();
        $this->appointments->save(AppointmentMother::booked('a1', '2026-01-05 09:00'));
        $this->notifier = new RecordingNotifier();
    }

    public function testBookingNotifiesTheCustomerAndTheOwner(): void
    {
        $appointment = AppointmentMother::booked('a2', '2026-01-05 10:00');
        $event = new AppointmentBooked($appointment->id(), $appointment->providerId(), $appointment->customer(), $appointment->slot(), new \DateTimeImmutable());

        (new NotifyOnAppointmentBooked($this->providers, self::owners(['owner-1' => 'owner@example.com']), $this->notifier))($event);

        self::assertSame(['jane@example.com', 'owner@example.com'], $this->recipients());
        self::assertStringContainsString('Cabinet Martin', $this->notifier->sent[0]->subject);
        self::assertStringStartsWith('Nouvelle demande', $this->notifier->sent[1]->subject);
    }

    public function testBookingStillNotifiesTheCustomerWhenTheOwnerHasNoAddress(): void
    {
        $appointment = AppointmentMother::booked('a2', '2026-01-05 10:00');
        $event = new AppointmentBooked($appointment->id(), $appointment->providerId(), $appointment->customer(), $appointment->slot(), new \DateTimeImmutable());

        (new NotifyOnAppointmentBooked($this->providers, self::owners([]), $this->notifier))($event);

        self::assertSame(['jane@example.com'], $this->recipients());
    }

    public function testConfirmationNotifiesTheCustomer(): void
    {
        $appointment = $this->appointments->get(new \App\Scheduling\Domain\Appointment\AppointmentId('a1'));

        (new NotifyOnAppointmentConfirmed($this->appointments, $this->providers, $this->notifier))(
            new AppointmentConfirmed($appointment->id(), new \DateTimeImmutable()),
        );

        self::assertSame(['jane@example.com'], $this->recipients());
        self::assertSame('Rendez-vous confirmé – Cabinet Martin', $this->notifier->sent[0]->subject);
    }

    public function testCancellationNotifiesTheCustomerWithTheReason(): void
    {
        $appointment = $this->appointments->get(new \App\Scheduling\Domain\Appointment\AppointmentId('a1'));

        (new NotifyOnAppointmentCancelled($this->appointments, $this->providers, $this->notifier))(
            new AppointmentCancelled($appointment->id(), 'Empêchement', new \DateTimeImmutable()),
        );

        self::assertSame(['jane@example.com'], $this->recipients());
        self::assertStringContainsString('Motif : Empêchement', $this->notifier->sent[0]->body);
    }

    /** @return list<string> */
    private function recipients(): array
    {
        return array_map(static fn ($n): string => $n->recipient, $this->notifier->sent);
    }

    /**
     * @param array<string, string> $emails
     */
    private static function owners(array $emails): OwnerDirectory
    {
        return new class($emails) implements OwnerDirectory {
            /** @param array<string, string> $emails */
            public function __construct(private readonly array $emails)
            {
            }

            public function emailOf(OwnerId $ownerId): ?string
            {
                return $this->emails[$ownerId->value] ?? null;
            }
        };
    }
}
