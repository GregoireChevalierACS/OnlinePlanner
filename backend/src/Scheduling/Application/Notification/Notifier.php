<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Notification;

/**
 * Port d'envoi : chaque canal (Symfony Mailer, fournisseur SMS…) en est un adapter.
 */
interface Notifier
{
    public function send(Notification $notification): void;
}
