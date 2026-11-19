<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Domain\Account;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Adapter : présente un compte du domaine au composant Security, sans que le domaine en dépende.
 */
final readonly class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const string ROLE = 'ROLE_PROVIDER';

    /**
     * @param non-empty-string $email
     */
    private function __construct(
        public string $id,
        private string $email,
        private string $passwordHash,
        public bool $emailVerified,
    ) {
    }

    public static function fromAccount(Account $account): self
    {
        $email = $account->email();
        \assert('' !== $email);

        return new self($account->id()->value, $email, $account->passwordHash(), $account->isEmailVerified());
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return [self::ROLE];
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }
}
