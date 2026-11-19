<?php

declare(strict_types=1);

namespace App\Identity\Domain;

/**
 * Template Method : cycle de vie commun des liens envoyés par e-mail (usage unique, expiration).
 * Chaque sorte de lien ne précise que sa durée de validité.
 * Seule l'empreinte du jeton est conservée ; le jeton lui-même ne vit que dans l'e-mail.
 */
abstract class OneTimeToken
{
    private ?\DateTimeImmutable $usedAt = null;

    final protected function __construct(
        private readonly string $tokenHash,
        private readonly AccountId $accountId,
        private readonly \DateTimeImmutable $expiresAt,
    ) {
    }

    /** Durée de validité, au format de DateTimeImmutable::modify() (ex. « +1 hour »). */
    abstract protected static function validity(): string;

    public static function issue(string $tokenHash, AccountId $accountId, \DateTimeImmutable $now): static
    {
        return new static($tokenHash, $accountId, $now->modify(static::validity()));
    }

    /**
     * @throws InvalidLink si le lien a expiré ou a déjà servi
     */
    public function consume(\DateTimeImmutable $now): AccountId
    {
        if (null !== $this->usedAt || $now >= $this->expiresAt) {
            throw InvalidLink::create();
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
