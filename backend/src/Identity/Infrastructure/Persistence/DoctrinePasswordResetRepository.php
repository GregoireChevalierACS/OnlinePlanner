<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence;

use App\Identity\Domain\PasswordReset;
use App\Identity\Domain\PasswordResetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(PasswordResetRepository::class)]
final readonly class DoctrinePasswordResetRepository implements PasswordResetRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(PasswordReset $reset): void
    {
        $this->em->persist($reset);
        $this->em->flush();
    }

    public function findByTokenHash(string $tokenHash): ?PasswordReset
    {
        return $this->em->find(PasswordReset::class, $tokenHash);
    }
}
