<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Persistence\Doctrine;

use App\Scheduling\Domain\Exception\ProviderNotFound;
use App\Scheduling\Domain\Provider\Provider;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Scheduling\Domain\ProviderId;
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
}
