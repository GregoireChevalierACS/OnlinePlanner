<?php

declare(strict_types=1);

namespace App\Identity\Domain;

interface EmailVerificationRepository
{
    public function save(EmailVerification $verification): void;

    public function findByTokenHash(string $tokenHash): ?EmailVerification;
}
