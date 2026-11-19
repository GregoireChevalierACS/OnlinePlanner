<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence;

use App\Identity\Domain\EmailVerification;
use App\Identity\Domain\EmailVerificationRepository;

final class InMemoryEmailVerificationRepository implements EmailVerificationRepository
{
    /** @var array<string, EmailVerification> */
    private array $verifications = [];

    public function save(EmailVerification $verification): void
    {
        $this->verifications[$verification->tokenHash()] = clone $verification;
    }

    public function findByTokenHash(string $tokenHash): ?EmailVerification
    {
        $verification = $this->verifications[$tokenHash] ?? null;

        return null === $verification ? null : clone $verification;
    }
}
