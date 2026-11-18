<?php

declare(strict_types=1);

namespace App\Identity\Domain;

/**
 * Demande de réinitialisation : à usage unique et limitée dans le temps.
 * Seule l'empreinte du jeton est conservée ; le jeton lui-même ne vit que dans l'e-mail.
 */
final class PasswordReset
{
    public const string VALIDITY = '+1 hour';

    private ?\DateTimeImmutable $usedAt = null;

    private function __construct(
        private readonly string $tokenHash,
        private readonly AccountId $accountId,
        private readonly \DateTimeImmutable $expiresAt,
    ) {
    }

    public static function issue(string $tokenHash, AccountId $accountId, \DateTimeImmutable $now): self
    {
        return new self($tokenHash, $accountId, $now->modify(self::VALIDITY));
    }

    /**
     * @throws InvalidResetToken si le lien a expiré ou a déjà servi
     */
    public function consume(\DateTimeImmutable $now): AccountId
    {
        if (null !== $this->usedAt || $now >= $this->expiresAt) {
            throw InvalidResetToken::create();
        }
        $this->usedAt = $now;

        return $this->accountId;
    }

    public function tokenHash(): string
    {
        return $this->tokenHash;
    }

    public function accountId(): AccountId
    {
        return $this->accountId;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isUsed(): bool
    {
        return null !== $this->usedAt;
    }
}
