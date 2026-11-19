<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Identity\Domain\AccountRepository;
use App\Identity\Domain\InvalidLink;
use App\Identity\Domain\PasswordResetRepository;
use App\Identity\Domain\PlainPassword;
use App\Shared\Application\CommandHandler;
use App\Shared\Domain\Clock;

final readonly class ResetPasswordHandler implements CommandHandler
{
    public function __construct(
        private PasswordResetRepository $resets,
        private AccountRepository $accounts,
        private PasswordHasher $hasher,
        private Clock $clock,
    ) {
    }

    public function __invoke(ResetPassword $command): void
    {
        $password = new PlainPassword($command->password);
        $reset = $this->resets->findByTokenHash(TokenHash::of($command->token)) ?? throw InvalidLink::create();
        $account = $this->accounts->find($reset->consume($this->clock->now())) ?? throw InvalidLink::create();

        $account->changePassword($this->hasher->hash($password));
        $this->accounts->save($account);
        $this->resets->save($reset);
    }
}
