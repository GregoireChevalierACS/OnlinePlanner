<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Shared\Application\Notification\Notification;

final class AccountEmails
{
    public static function passwordReset(string $email, string $link): Notification
    {
        return new Notification(
            $email,
            'Réinitialisation de votre mot de passe',
            "Bonjour,\n\nPour choisir un nouveau mot de passe, ouvrez ce lien (valable 1 heure, utilisable une seule fois) :\n"
            .$link
            ."\n\nSi vous n'avez rien demandé, ignorez cet e-mail : votre mot de passe actuel reste valable."
            ."\n\nÀ bientôt,\nOnlinePlanner",
        );
    }
}
