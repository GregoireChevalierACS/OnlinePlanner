<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence;

use App\Identity\Domain\Account;
use App\Identity\Domain\AccountRepository;
use App\Identity\Domain\EmailAlreadyUsed;

final class InMemoryAccountRepository implements AccountRepository
{
    /** @var array<string, Account> clé : identifiant */
    private array $accounts = [];

    public function save(Account $account): void
    {
        foreach ($this->accounts as $other) {
            if ($other->email() === $account->email() && !$other->id()->equals($account->id())) {
                throw EmailAlreadyUsed::create();
            }
        }
        $this->accounts[$account->id()->value] = clone $account;
    }

    public function findByEmail(string $normalizedEmail): ?Account
    {
        foreach ($this->accounts as $account) {
            if ($account->email() === $normalizedEmail) {
                return clone $account;
            }
        }

        return null;
    }
}
