<?php

declare(strict_types=1);

namespace App\Tests\Contract\Identity;

use App\Identity\Domain\Account;
use App\Identity\Domain\AccountId;
use App\Identity\Domain\AccountRepository;
use App\Identity\Domain\EmailVerification;
use App\Identity\Domain\EmailVerificationRepository;

trait EmailVerificationRepositoryContract
{
    abstract protected function repository(): AccountRepository;

    abstract protected function verifications(): EmailVerificationRepository;

    abstract protected function clear(): void;

    public function testStoresAndConsumesAVerificationLink(): void
    {
        $repository = $this->verifications();
        $verification = EmailVerification::issue(str_repeat('c', 64), new AccountId('acc-1'), new \DateTimeImmutable('2026-01-05 10:00:00'));
        $repository->save($verification);
        $this->clear();

        $found = $repository->findByTokenHash(str_repeat('c', 64));
        self::assertInstanceOf(EmailVerification::class, $found);
        self::assertSame('2026-01-07 10:00:00', $found->expiresAt()->format('Y-m-d H:i:s'));
        self::assertNull($repository->findByTokenHash(str_repeat('d', 64)));

        $found->consume(new \DateTimeImmutable('2026-01-06'));
        $repository->save($found);
        $this->clear();
        self::assertTrue($repository->findByTokenHash(str_repeat('c', 64))?->isUsed());
    }

    public function testRemembersThatAnAccountIsVerified(): void
    {
        $accounts = $this->repository();
        $account = Account::register(new AccountId('acc-1'), 'jane@example.com', 'hash');
        $accounts->save($account);
        $this->clear();
        self::assertFalse($accounts->find(new AccountId('acc-1'))?->isEmailVerified());

        // L'assertion précédente garantit (y compris pour PHPStan) que le compte a été retrouvé.
        $reloaded = $accounts->find(new AccountId('acc-1'));
        $reloaded->verifyEmail(new \DateTimeImmutable('2026-01-05'));
        $accounts->save($reloaded);
        $this->clear();

        self::assertTrue($accounts->find(new AccountId('acc-1'))?->isEmailVerified());
    }
}
