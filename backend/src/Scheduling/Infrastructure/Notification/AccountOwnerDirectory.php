<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Notification;

use App\Identity\Domain\AccountId;
use App\Identity\Domain\AccountRepository;
use App\Scheduling\Application\Notification\OwnerDirectory;
use App\Scheduling\Domain\Provider\OwnerId;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Anti-corruption layer : traduit un OwnerId de Scheduling en compte du contexte Identity.
 */
#[AsAlias(OwnerDirectory::class)]
final readonly class AccountOwnerDirectory implements OwnerDirectory
{
    public function __construct(private AccountRepository $accounts)
    {
    }

    public function emailOf(OwnerId $ownerId): ?string
    {
        return $this->accounts->find(new AccountId($ownerId->value))?->email();
    }
}
