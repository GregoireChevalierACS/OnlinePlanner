<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Shared\Application\Notification\Notification;

final class AccountEmails
{
    public static function emailVerification(string $email, string $link): Notification
    {
        return new Notification(
            $email,
            'Confirmez votre adresse e-mail',
            "Bonjour,\n\nPour confirmer que cette adresse est bien la vôtre, ouvrez ce lien (valable 2 jours) :\n"
            .$link
            ."\n\nTant que l'adresse n'est pas confirmée, vous ne recevez pas les e-mails de nouvelles réservations."
            ."\nSi vous n'avez pas créé de compte, ignorez cet e-mail."
            ."\n\nÀ bientôt,\nOnlinePlanner",
        );
    }

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
