<?php

declare(strict_types=1);

namespace App\Identity\Domain;

/**
 * Lien de vérification de l'adresse : il prouve seulement que le compte reçoit ses e-mails,
 * on peut donc le laisser valable plus longtemps.
 */
final class EmailVerification extends OneTimeToken
{
    protected static function validity(): string
    {
        return '+2 days';
    }
}
