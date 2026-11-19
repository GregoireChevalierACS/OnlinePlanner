<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Identity\Domain\Account;
use App\Identity\Domain\AccountId;
use App\Identity\Domain\AccountRepository;
use App\Identity\Domain\EmailAlreadyUsed;
use App\Identity\Domain\PlainPassword;
use App\Shared\Application\CommandHandler;

final readonly class RegisterAccountHandler implements CommandHandler
{
    public function __construct(
        private AccountRepository $accounts,
        private PasswordHasher $hasher,
        private EmailVerificationSender $verification,
    ) {
    }

    public function __invoke(RegisterAccount $command): void
    {
        $email = Account::normalize($command->email);
        $password = new PlainPassword($command->password);
        if (null !== $this->accounts->findByEmail($email)) {
            throw EmailAlreadyUsed::create();
        }

        $account = Account::register(new AccountId($command->accountId), $email, $this->hasher->hash($password));
        $this->accounts->save($account);
        $this->verification->sendTo($account);
    }
}
