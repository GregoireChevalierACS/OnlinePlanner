<?php

declare(strict_types=1);

namespace App\Tests\Contract\Identity;

use App\Identity\Domain\AccountId;
use App\Identity\Domain\PasswordReset;
use App\Identity\Domain\PasswordResetRepository;

trait PasswordResetRepositoryContract
{
    abstract protected function resets(): PasswordResetRepository;

    abstract protected function clear(): void;

    public function testFindsAResetByTheHashOfItsToken(): void
    {
        $repository = $this->resets();
        $repository->save(PasswordReset::issue(str_repeat('a', 64), new AccountId('acc-1'), new \DateTimeImmutable('2026-01-05 10:00:00')));
        $this->clear();

        $found = $repository->findByTokenHash(str_repeat('a', 64));

        self::assertNotNull($found);
        self::assertSame('acc-1', $found->accountId()->value);
        self::assertSame('2026-01-05 11:00:00', $found->expiresAt()->format('Y-m-d H:i:s'));
        self::assertFalse($found->isUsed());
        self::assertNull($repository->findByTokenHash(str_repeat('b', 64)));
    }

    public function testRemembersThatAResetWasUsed(): void
    {
        $repository = $this->resets();
        $reset = PasswordReset::issue(str_repeat('a', 64), new AccountId('acc-1'), new \DateTimeImmutable('2026-01-05 10:00:00'));
        $repository->save($reset);

        $reset->consume(new \DateTimeImmutable('2026-01-05 10:30:00'));
        $repository->save($reset);
        $this->clear();

        self::assertTrue($repository->findByTokenHash(str_repeat('a', 64))?->isUsed());
    }
}
