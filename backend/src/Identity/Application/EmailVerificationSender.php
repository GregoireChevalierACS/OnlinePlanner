<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Identity\Domain\Account;
use App\Identity\Domain\EmailVerification;
use App\Identity\Domain\EmailVerificationRepository;
use App\Shared\Application\Notification\Notifier;
use App\Shared\Domain\Clock;

/**
 * Service applicatif partagé par l'inscription et le renvoi du lien.
 */
final readonly class EmailVerificationSender
{
    public function __construct(
        private EmailVerificationRepository $verifications,
        private SecretLinks $links,
        private Notifier $notifier,
        private Clock $clock,
    ) {
    }

    public function sendTo(Account $account): void
    {
        $token = $this->links->generate();
        $this->verifications->save(EmailVerification::issue(TokenHash::of($token), $account->id(), $this->clock->now()));
        $this->notifier->send(AccountEmails::emailVerification($account->email(), $this->links->emailVerificationLink($token)));
    }
}
