<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence;

use App\Identity\Domain\AccountId;
use App\Shared\Infrastructure\Persistence\Doctrine\IdentifierType;

final class AccountIdType extends IdentifierType
{
    public const string NAME = 'account_id';

    protected function identifierClass(): string
    {
        return AccountId::class;
    }
}
