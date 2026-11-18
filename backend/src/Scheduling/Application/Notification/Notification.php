<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Notification;

/**
 * Message à envoyer, indépendant du canal (e-mail aujourd'hui, SMS demain).
 */
final readonly class Notification
{
    public function __construct(
        public string $recipient,
        public string $subject,
        public string $body,
    ) {
    }
}
