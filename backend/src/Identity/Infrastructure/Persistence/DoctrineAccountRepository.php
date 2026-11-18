<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence;

use App\Identity\Domain\Account;
use App\Identity\Domain\AccountId;
use App\Identity\Domain\AccountRepository;
use App\Identity\Domain\EmailAlreadyUsed;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(AccountRepository::class)]
final readonly class DoctrineAccountRepository implements AccountRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(Account $account): void
    {
        $this->em->persist($account);
        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            // Deux inscriptions simultanées avec la même adresse : l'index unique tranche.
            throw EmailAlreadyUsed::create();
        }
    }

    public function findByEmail(string $normalizedEmail): ?Account
    {
        return $this->em->getRepository(Account::class)->findOneBy(['email' => $normalizedEmail]);
    }

    public function find(AccountId $id): ?Account
    {
        return $this->em->find(Account::class, $id);
    }
}
