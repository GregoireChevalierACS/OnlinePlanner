<?php

declare(strict_types=1);

namespace App\Tests\Contract\Identity;

use App\Identity\Domain\Account;
use App\Identity\Domain\AccountId;
use App\Identity\Domain\AccountRepository;
use App\Identity\Domain\EmailAlreadyUsed;

trait AccountRepositoryContract
{
    abstract protected function repository(): AccountRepository;

    abstract protected function clear(): void;

    public function testFindsASavedAccountByEmail(): void
    {
        $repository = $this->repository();
        $repository->save(Account::register(new AccountId('acc-1'), 'jane@example.com', 'hash'));
        $this->clear();

        $found = $repository->findByEmail('jane@example.com');

        self::assertNotNull($found);
        self::assertSame('acc-1', $found->id()->value);
        self::assertSame('hash', $found->passwordHash());
        self::assertNull($repository->findByEmail('john@example.com'));
    }

    public function testRefusesASecondAccountWithTheSameEmail(): void
    {
        $repository = $this->repository();
        $repository->save(Account::register(new AccountId('acc-1'), 'jane@example.com', 'hash'));

        $this->expectException(EmailAlreadyUsed::class);

        $repository->save(Account::register(new AccountId('acc-2'), 'jane@example.com', 'other'));
    }
}
