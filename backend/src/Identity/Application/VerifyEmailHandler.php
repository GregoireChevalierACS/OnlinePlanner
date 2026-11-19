<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Identity\Domain\AccountRepository;
use App\Identity\Domain\EmailVerificationRepository;
use App\Identity\Domain\InvalidLink;
use App\Shared\Application\CommandHandler;
use App\Shared\Domain\Clock;

final readonly class VerifyEmailHandler implements CommandHandler
{
    public function __construct(
        private EmailVerificationRepository $verifications,
        private AccountRepository $accounts,
        private Clock $clock,
    ) {
    }

    public function __invoke(VerifyEmail $command): void
    {
        $verification = $this->verifications->findByTokenHash(TokenHash::of($command->token)) ?? throw InvalidLink::create();
        $now = $this->clock->now();
        $account = $this->accounts->find($verification->consume($now)) ?? throw InvalidLink::create();

        $account->verifyEmail($now);
        $this->accounts->save($account);
        $this->verifications->save($verification);
    }
}
