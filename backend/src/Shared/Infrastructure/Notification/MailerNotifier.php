<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Notification;

use App\Shared\Application\Notification\Notification;
use App\Shared\Application\Notification\Notifier;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Adapter e-mail du port Notifier (Symfony Mailer ; Mailpit en développement).
 */
#[AsAlias(Notifier::class)]
final readonly class MailerNotifier implements Notifier
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire(env: 'MAILER_FROM')] private string $from,
    ) {
    }

    public function send(Notification $notification): void
    {
        $this->mailer->send(
            (new Email())
                ->from(Address::create($this->from))
                ->to($notification->recipient)
                ->subject($notification->subject)
                ->text($notification->body),
        );
    }
}
