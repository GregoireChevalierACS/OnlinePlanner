<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Domain\Account;
use App\Identity\Domain\AccountRepository;
use App\Identity\Domain\InvalidCredentials;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Charge l'utilisateur connecté depuis le dépôt de comptes (par e-mail).
 *
 * @implements UserProviderInterface<SecurityUser>
 */
final readonly class AccountUserProvider implements UserProviderInterface
{
    public function __construct(private AccountRepository $accounts)
    {
    }

    public function loadUserByIdentifier(string $identifier): SecurityUser
    {
        try {
            $account = $this->accounts->findByEmail(Account::normalize($identifier));
        } catch (InvalidCredentials) {
            $account = null;
        }
        if (null === $account) {
            $exception = new UserNotFoundException();
            $exception->setUserIdentifier($identifier);
            throw $exception;
        }

        return SecurityUser::fromAccount($account);
    }

    public function refreshUser(UserInterface $user): SecurityUser
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException($user::class);
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SecurityUser::class === $class;
    }
}
