<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Identity\Domain\AccountId;
use App\Identity\Domain\AccountRepository;
use App\Shared\Application\CommandHandler;

final readonly class ResendEmailVerificationHandler implements CommandHandler
{
    public function __construct(
        private AccountRepository $accounts,
        private EmailVerificationSender $verification,
    ) {
    }

    public function __invoke(ResendEmailVerification $command): void
    {
        $account = $this->accounts->find(new AccountId($command->accountId));
        if (null === $account || $account->isEmailVerified()) {
            return;
        }

        $this->verification->sendTo($account);
    }
}
