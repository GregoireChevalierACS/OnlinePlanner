<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Application;

use App\Scheduling\Application\Query\ListOwnedProviders;
use App\Scheduling\Application\Query\ListOwnedProvidersHandler;
use App\Scheduling\Application\Query\ProviderView;
use App\Scheduling\Infrastructure\Persistence\InMemory\InMemoryProviderRepository;
use App\Tests\Fixture\ProviderMother;
use PHPUnit\Framework\TestCase;

final class ListOwnedProvidersHandlerTest extends TestCase
{
    public function testListsTheAccountsProviders(): void
    {
        $providers = new InMemoryProviderRepository();
        $providers->save(ProviderMother::doctor('p1', ownerId: 'owner-1', name: 'Cabinet'));
        $providers->save(ProviderMother::doctor('p2', ownerId: 'owner-2'));

        $views = (new ListOwnedProvidersHandler($providers))(new ListOwnedProviders('owner-1'));

        self::assertEquals([new ProviderView('p1', 'Cabinet')], $views);
    }
}
