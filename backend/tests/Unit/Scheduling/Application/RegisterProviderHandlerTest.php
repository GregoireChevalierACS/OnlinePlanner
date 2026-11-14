<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Application;

use App\Scheduling\Application\Command\RegisterProvider;
use App\Scheduling\Application\Command\RegisterProviderHandler;
use App\Scheduling\Domain\ProviderId;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryProviderRepository;
use PHPUnit\Framework\TestCase;

final class RegisterProviderHandlerTest extends TestCase
{
    public function testRegistersProviderWithScheduleAndSettings(): void
    {
        $providers = new InMemoryProviderRepository();

        (new RegisterProviderHandler($providers))(new RegisterProvider(
            'p1',
            'Dr Martin',
            [
                ['weekday' => 2, 'opensAt' => '14:00', 'closesAt' => '18:00'],
                ['weekday' => 1, 'opensAt' => '09:00', 'closesAt' => '12:00'],
            ],
            20,
            5,
            120,
        ));

        $provider = $providers->get(new ProviderId('p1'));
        self::assertSame('Dr Martin', $provider->name());
        self::assertSame(20, $provider->settings()->durationMinutes);
        self::assertSame(5, $provider->settings()->bufferMinutes);
        self::assertSame(120, $provider->settings()->minimumNoticeMinutes);
        self::assertSame(
            ['1 09:00-12:00', '2 14:00-18:00'],
            array_map(
                static fn ($h): string => \sprintf('%d %s-%s', $h->weekday, $h->opensAt(), $h->closesAt()),
                $provider->schedule()->openingHours,
            ),
        );
    }

    public function testDefaults(): void
    {
        $command = new RegisterProvider('p1', 'Dr Martin', [], 30);

        self::assertSame(0, $command->bufferMinutes);
        self::assertSame(0, $command->minimumNoticeMinutes);
    }
}
