<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence;

use App\Identity\Domain\PasswordReset;
use App\Identity\Domain\PasswordResetRepository;

final class InMemoryPasswordResetRepository implements PasswordResetRepository
{
    /** @var array<string, PasswordReset> */
    private array $resets = [];

    public function save(PasswordReset $reset): void
    {
        $this->resets[$reset->tokenHash()] = clone $reset;
    }

    public function findByTokenHash(string $tokenHash): ?PasswordReset
    {
        $reset = $this->resets[$tokenHash] ?? null;

        return null === $reset ? null : clone $reset;
    }
}
