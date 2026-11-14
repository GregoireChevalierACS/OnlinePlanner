<?php

declare(strict_types=1);

namespace App\Tests\Contract\Scheduling;

use App\Scheduling\Domain\Exception\ProviderNotFound;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Scheduling\Domain\ProviderId;
use App\Tests\Fixture\ProviderMother;
use PHPUnit\Framework\TestCase;

abstract class ProviderRepositoryContract extends TestCase
{
    abstract protected function repository(): ProviderRepository;

    public function testSavedProviderCanBeRetrieved(): void
    {
        $repository = $this->repository();
        $repository->save(ProviderMother::doctor('p1', 45));

        $found = $repository->get(new ProviderId('p1'));

        self::assertSame('p1', $found->id()->value);
        self::assertSame('Dr Martin', $found->name());
        self::assertSame(30, $found->settings()->durationMinutes);
        self::assertSame(0, $found->settings()->bufferMinutes);
        self::assertSame(45, $found->settings()->minimumNoticeMinutes);
        self::assertCount(1, $found->schedule()->openingHours);
        self::assertSame(1, $found->schedule()->openingHours[0]->weekday);
        self::assertSame('09:00', $found->schedule()->openingHours[0]->opensAt());
        self::assertSame('12:00', $found->schedule()->openingHours[0]->closesAt());
    }

    public function testUnknownProviderThrows(): void
    {
        $this->expectException(ProviderNotFound::class);
        $this->expectExceptionMessage('Prestataire « inconnu » introuvable.');

        $this->repository()->get(new ProviderId('inconnu'));
    }
}
