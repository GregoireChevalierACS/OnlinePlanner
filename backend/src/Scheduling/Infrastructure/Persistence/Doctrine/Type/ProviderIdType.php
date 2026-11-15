<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Persistence\Doctrine\Type;

use App\Scheduling\Domain\ProviderId;
use App\Shared\Infrastructure\Persistence\Doctrine\IdentifierType;

final class ProviderIdType extends IdentifierType
{
    public const string NAME = 'provider_id';

    protected function identifierClass(): string
    {
        return ProviderId::class;
    }
}
