<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Persistence\Doctrine;

use App\Scheduling\Domain\Exception\ProviderNotFound;
use App\Scheduling\Domain\Provider\OwnerId;
use App\Scheduling\Domain\Provider\Provider;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Scheduling\Domain\ProviderId;
use App\Scheduling\Infrastructure\Persistence\Doctrine\Type\OwnerIdType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ProviderRepository::class)]
final readonly class DoctrineProviderRepository implements ProviderRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(Provider $provider): void
    {
        $this->em->persist($provider);
        $this->em->flush();
    }

    public function get(ProviderId $id): Provider
    {
        return $this->em->find(Provider::class, $id) ?? throw ProviderNotFound::withId($id);
    }

    public function ownedBy(OwnerId $ownerId): array
    {
        /** @var list<Provider> $providers */
        $providers = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Provider::class, 'p')
            ->where('p.ownerId = :owner')
            ->orderBy('p.name', 'ASC')
            ->setParameter('owner', $ownerId, OwnerIdType::NAME)
            ->getQuery()
            ->getResult();

        return $providers;
    }
}
