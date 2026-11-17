<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Persistence\Doctrine\Type;

use App\Scheduling\Domain\Provider\OwnerId;
use App\Shared\Infrastructure\Persistence\Doctrine\IdentifierType;

final class OwnerIdType extends IdentifierType
{
    public const string NAME = 'owner_id';

    protected function identifierClass(): string
    {
        return OwnerId::class;
    }
}
