<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Identity\Domain\Account;
use App\Identity\Domain\AccountRepository;
use App\Identity\Domain\InvalidCredentials;
use App\Identity\Domain\PasswordReset;
use App\Identity\Domain\PasswordResetRepository;
use App\Shared\Application\CommandHandler;
use App\Shared\Application\Notification\Notifier;
use App\Shared\Domain\Clock;

/**
 * Envoie un lien à usage unique si l'adresse correspond à un compte.
 * Sinon, ne fait rien : l'appelant reçoit la même réponse dans les deux cas.
 */
final readonly class RequestPasswordResetHandler implements CommandHandler
{
    public function __construct(
        private AccountRepository $accounts,
        private PasswordResetRepository $resets,
        private SecretLinks $links,
        private Notifier $notifier,
        private Clock $clock,
    ) {
    }

    public function __invoke(RequestPasswordReset $command): void
    {
        try {
            $account = $this->accounts->findByEmail(Account::normalize($command->email));
        } catch (InvalidCredentials) {
            return;
        }
        if (null === $account) {
            return;
        }

        $token = $this->links->generate();
        $this->resets->save(PasswordReset::issue(TokenHash::of($token), $account->id(), $this->clock->now()));
        $this->notifier->send(AccountEmails::passwordReset($account->email(), $this->links->passwordResetLink($token)));
    }
}
