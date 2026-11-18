<?php

declare(strict_types=1);

namespace App\Identity\Application;

/**
 * Empreinte stockée à la place du jeton : une fuite de la base ne donne aucun lien utilisable.
 */
final class TokenHash
{
    public static function of(#[\SensitiveParameter] string $token): string
    {
        return hash('sha256', $token);
    }
}
