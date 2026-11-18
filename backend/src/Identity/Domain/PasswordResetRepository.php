<?php

declare(strict_types=1);

namespace App\Identity\Domain;

interface PasswordResetRepository
{
    public function save(PasswordReset $reset): void;

    public function findByTokenHash(string $tokenHash): ?PasswordReset;
}
