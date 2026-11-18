<?php

declare(strict_types=1);

namespace App\Identity\Application;

/**
 * Port : jetons secrets (aléatoires, imprévisibles) et lien envoyé par e-mail.
 */
interface ResetTokens
{
    /** Nouveau jeton secret, à n'envoyer qu'à l'adresse du compte. */
    public function generate(): string;

    /** Lien de la page « nouveau mot de passe » du front, portant le jeton. */
    public function linkFor(string $token): string;
}
