<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Appointment\Status;

use App\Scheduling\Domain\Appointment\Status\AppointmentStatus;
use App\Scheduling\Domain\Appointment\Status\AppointmentStatuses;
use App\Scheduling\Domain\Appointment\Status\Cancelled;
use App\Scheduling\Domain\Appointment\Status\Completed;
use App\Scheduling\Domain\Appointment\Status\Confirmed;
use App\Scheduling\Domain\Appointment\Status\Pending;
use App\Scheduling\Domain\Exception\InvalidStatusTransition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AppointmentStatusTest extends TestCase
{
    /**
     * @param 'confirm'|'cancel'|'complete'   $action
     * @param class-string<AppointmentStatus> $expected
     */
    #[DataProvider('allowedTransitions')]
    public function testAllowedTransitions(AppointmentStatus $from, string $action, string $expected): void
    {
        self::assertInstanceOf($expected, $from->{$action}());
    }

    /** @return iterable<string, array{AppointmentStatus, string, class-string<AppointmentStatus>}> */
    public static function allowedTransitions(): iterable
    {
        yield 'pending → confirmed' => [new Pending(), 'confirm', Confirmed::class];
        yield 'pending → cancelled' => [new Pending(), 'cancel', Cancelled::class];
        yield 'confirmed → cancelled' => [new Confirmed(), 'cancel', Cancelled::class];
        yield 'confirmed → completed' => [new Confirmed(), 'complete', Completed::class];
    }

    /**
     * @param 'confirm'|'cancel'|'complete' $action
     */
    #[DataProvider('forbiddenTransitions')]
    public function testForbiddenTransitions(AppointmentStatus $from, string $action, string $message): void
    {
        $this->expectException(InvalidStatusTransition::class);
        $this->expectExceptionMessage($message);

        $from->{$action}();
    }

    /** @return iterable<string, array{AppointmentStatus, string, string}> */
    public static function forbiddenTransitions(): iterable
    {
        yield 'pending ✗ complete' => [new Pending(), 'complete', 'Action « terminer » impossible sur un rendez-vous au statut « pending ».'];
        yield 'confirmed ✗ confirm' => [new Confirmed(), 'confirm', 'Action « confirmer » impossible sur un rendez-vous au statut « confirmed ».'];
        yield 'cancelled ✗ confirm' => [new Cancelled(), 'confirm', '« confirmer »'];
        yield 'cancelled ✗ cancel' => [new Cancelled(), 'cancel', 'Action « annuler » impossible sur un rendez-vous au statut « cancelled ».'];
        yield 'cancelled ✗ complete' => [new Cancelled(), 'complete', '« terminer »'];
        yield 'completed ✗ confirm' => [new Completed(), 'confirm', '« completed »'];
        yield 'completed ✗ cancel' => [new Completed(), 'cancel', '« annuler »'];
        yield 'completed ✗ complete' => [new Completed(), 'complete', '« terminer »'];
    }

    #[DataProvider('statuses')]
    public function testFactoryRebuildsEachStatusFromItsName(AppointmentStatus $status, string $name, bool $blocksSlot): void
    {
        self::assertSame($name, $status->name());
        self::assertSame($blocksSlot, $status->blocksSlot());
        self::assertEquals($status, AppointmentStatuses::fromName($name));
    }

    /** @return iterable<array{AppointmentStatus, string, bool}> */
    public static function statuses(): iterable
    {
        yield [new Pending(), 'pending', true];
        yield [new Confirmed(), 'confirmed', true];
        yield [new Cancelled(), 'cancelled', false];
        yield [new Completed(), 'completed', true];
    }

    public function testFactoryRejectsUnknownName(): void
    {
        $this->expectException(InvalidStatusTransition::class);
        $this->expectExceptionMessage('Statut de rendez-vous inconnu : « archived ».');

        AppointmentStatuses::fromName('archived');
    }
}
