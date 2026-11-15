<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Persistence\Doctrine;

use App\Scheduling\Domain\Appointment\Appointment;
use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Exception\AppointmentNotFound;
use App\Scheduling\Domain\Exception\SlotUnavailable;
use App\Scheduling\Domain\Provider\Provider;
use App\Scheduling\Domain\ProviderId;
use App\Scheduling\Infrastructure\Persistence\Doctrine\Type\ProviderIdType;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(AppointmentRepository::class)]
final readonly class DoctrineAppointmentRepository implements AppointmentRepository
{
    /** Violation de la contrainte d'exclusion `appointment_no_overlap` (PostgreSQL). */
    private const string EXCLUSION_VIOLATION = '23P01';

    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(Appointment $appointment): void
    {
        $this->em->persist($appointment);
        try {
            $this->em->flush();
        } catch (DriverException $e) {
            if (self::EXCLUSION_VIOLATION === $e->getSQLState()) {
                // Deux réservations simultanées : la base tranche, on renvoie la même erreur métier.
                throw SlotUnavailable::because([Provider::RULE_ALREADY_TAKEN]);
            }
            throw $e;
        }
    }

    public function get(AppointmentId $id): Appointment
    {
        return $this->em->find(Appointment::class, $id) ?? throw AppointmentNotFound::withId($id);
    }

    public function overlapping(ProviderId $providerId, TimeSlot $period): array
    {
        /** @var list<Appointment> $appointments */
        $appointments = $this->em->createQueryBuilder()
            ->select('a')
            ->from(Appointment::class, 'a')
            ->where('a.providerId = :provider')
            ->andWhere('a.slot.start < :end')
            ->andWhere('a.slot.end > :start')
            ->orderBy('a.slot.start', 'ASC')
            ->setParameter('provider', $providerId, ProviderIdType::NAME)
            ->setParameter('start', $period->start, Types::DATETIME_IMMUTABLE)
            ->setParameter('end', $period->end, Types::DATETIME_IMMUTABLE)
            ->getQuery()
            ->getResult();

        return $appointments;
    }
}
