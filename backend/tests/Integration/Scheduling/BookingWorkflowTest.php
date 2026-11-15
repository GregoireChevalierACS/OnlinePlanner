<?php

declare(strict_types=1);

namespace App\Tests\Integration\Scheduling;

use App\Scheduling\Application\Command\BookAppointment;
use App\Scheduling\Application\Command\CancelAppointment;
use App\Scheduling\Application\Command\ConfirmAppointment;
use App\Scheduling\Application\Command\RegisterProvider;
use App\Scheduling\Application\Query\AppointmentView;
use App\Scheduling\Application\Query\GetAppointment;
use App\Scheduling\Application\Query\ListAppointments;
use App\Scheduling\Application\Query\ListAvailableSlots;
use App\Scheduling\Application\Query\SlotView;
use App\Scheduling\Domain\Exception\AppointmentNotFound;
use App\Scheduling\Domain\Exception\SlotUnavailable;
use App\Scheduling\Domain\Provider\Provider;
use App\Shared\Application\CommandBus;
use App\Shared\Application\QueryBus;
use App\Shared\Infrastructure\Bus\MessengerCommandBus;
use App\Shared\Infrastructure\Bus\MessengerQueryBus;
use App\Shared\Infrastructure\Id\UuidGenerator;
use App\Tests\ContainerAccess;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

/**
 * Parcours complet par les vrais adapters : bus Messenger, transaction Doctrine, PostgreSQL.
 */
final class BookingWorkflowTest extends KernelTestCase
{
    use ClockSensitiveTrait;
    use ContainerAccess;

    private CommandBus $commands;
    private QueryBus $queries;

    protected function setUp(): void
    {
        self::mockTime('2026-01-05 07:00');
        $this->commands = self::service(MessengerCommandBus::class);
        $this->queries = self::service(MessengerQueryBus::class);

        $this->commands->dispatch(new RegisterProvider(
            'p1',
            'Dr Martin',
            [['weekday' => 1, 'opensAt' => '09:00', 'closesAt' => '10:00']],
            30,
            0,
            60,
        ));
    }

    public function testBookConfirmCancelThroughTheBuses(): void
    {
        $id = self::service(UuidGenerator::class)->next();
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id);

        $this->commands->dispatch(new BookAppointment($id, 'p1', new \DateTimeImmutable('2026-01-05 09:00'), 'Jane Doe', 'jane@example.com'));
        self::assertSame(['09:30'], $this->availableStarts());

        $this->commands->dispatch(new ConfirmAppointment($id));
        self::assertSame('confirmed', $this->appointment($id)->status);

        $this->commands->dispatch(new CancelAppointment($id, 'Empêchement'));
        self::assertSame('cancelled', $this->appointment($id)->status);
        self::assertSame(['09:00', '09:30'], $this->availableStarts());

        $agenda = $this->queries->ask(new ListAppointments('p1', new \DateTimeImmutable('2026-01-05'), new \DateTimeImmutable('2026-01-05')));
        self::assertIsArray($agenda);
        self::assertCount(1, $agenda);
    }

    public function testBusRethrowsTheOriginalDomainException(): void
    {
        $this->commands->dispatch(new BookAppointment('a1', 'p1', new \DateTimeImmutable('2026-01-05 09:30'), 'Jane', 'jane@example.com'));

        try {
            $this->commands->dispatch(new BookAppointment('a2', 'p1', new \DateTimeImmutable('2026-01-05 09:30'), 'John', 'john@example.com'));
            self::fail('SlotUnavailable attendue');
        } catch (SlotUnavailable $e) {
            self::assertSame([Provider::RULE_ALREADY_TAKEN], $e->reasons);
        }
    }

    public function testQueryBusRethrowsTheOriginalDomainException(): void
    {
        $this->expectException(AppointmentNotFound::class);

        $this->queries->ask(new GetAppointment('inconnu'));
    }

    /** @return list<string> */
    private function availableStarts(): array
    {
        $slots = $this->queries->ask(new ListAvailableSlots('p1', new \DateTimeImmutable('2026-01-05')));
        self::assertIsArray($slots);

        return array_map(static function (mixed $slot): string {
            self::assertInstanceOf(SlotView::class, $slot);

            return substr($slot->startsAt, 11, 5);
        }, array_values($slots));
    }

    private function appointment(string $id): AppointmentView
    {
        $view = $this->queries->ask(new GetAppointment($id));
        self::assertInstanceOf(AppointmentView::class, $view);

        return $view;
    }
}
