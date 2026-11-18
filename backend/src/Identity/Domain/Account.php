<?php

declare(strict_types=1);

namespace App\Identity\Domain;

use App\Shared\Domain\AggregateRoot;

/**
 * Aggregate Root : le compte d'un prestataire. Ne connaît que le hash du mot de passe.
 */
final class Account extends AggregateRoot
{
    private function __construct(
        private readonly AccountId $id,
        private readonly string $email,
        private string $passwordHash,
    ) {
    }

    public static function register(AccountId $id, string $email, string $passwordHash): self
    {
        return new self($id, self::normalize($email), $passwordHash);
    }

    /** Le changement de hash invalide aussi les sessions ouvertes (Symfony compare le mot de passe). */
    public function changePassword(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    /**
     * Forme canonique d'une adresse, utilisée pour l'inscription comme pour la connexion.
     */
    public static function normalize(string $email): string
    {
        $normalized = mb_strtolower(trim($email));
        if (false === filter_var($normalized, \FILTER_VALIDATE_EMAIL)) {
            throw InvalidCredentials::email($email);
        }

        return $normalized;
    }

    public function id(): AccountId
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }
}
