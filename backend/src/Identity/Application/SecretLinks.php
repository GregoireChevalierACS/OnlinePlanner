<?php

declare(strict_types=1);

namespace App\Identity\Application;

/**
 * Port : jetons secrets (aléatoires, imprévisibles) et liens du front qui les portent.
 */
interface SecretLinks
{
    /** Nouveau jeton secret, à n'envoyer qu'à l'adresse du compte. */
    public function generate(): string;

    /** Lien de la page « nouveau mot de passe ». */
    public function passwordResetLink(string $token): string;

    /** Lien de la page de vérification de l'adresse e-mail. */
    public function emailVerificationLink(string $token): string;
}
