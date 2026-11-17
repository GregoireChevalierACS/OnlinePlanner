<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Application\PasswordHasher;
use App\Identity\Domain\PlainPassword;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * Adapter du port PasswordHasher : même algorithme que celui utilisé à la connexion.
 */
#[AsAlias(PasswordHasher::class)]
final readonly class SymfonyPasswordHasher implements PasswordHasher
{
    public function __construct(private PasswordHasherFactoryInterface $factory)
    {
    }

    public function hash(PlainPassword $password): string
    {
        return $this->factory->getPasswordHasher(SecurityUser::class)->hash($password->value);
    }
}
