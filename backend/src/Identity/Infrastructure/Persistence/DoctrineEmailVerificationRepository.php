<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence;

use App\Identity\Domain\EmailVerification;
use App\Identity\Domain\EmailVerificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(EmailVerificationRepository::class)]
final readonly class DoctrineEmailVerificationRepository implements EmailVerificationRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(EmailVerification $verification): void
    {
        $this->em->persist($verification);
        $this->em->flush();
    }

    public function findByTokenHash(string $tokenHash): ?EmailVerification
    {
        return $this->em->find(EmailVerification::class, $tokenHash);
    }
}
