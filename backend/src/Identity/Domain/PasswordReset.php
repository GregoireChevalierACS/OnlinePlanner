<?php

declare(strict_types=1);

namespace App\Identity\Domain;

/**
 * Lien de réinitialisation du mot de passe : court, car il donne accès au compte.
 */
final class PasswordReset extends OneTimeToken
{
    protected static function validity(): string
    {
        return '+1 hour';
    }
}
