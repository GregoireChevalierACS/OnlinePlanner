<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Persistence\Doctrine\Type;

use App\Scheduling\Domain\Appointment\Status\AppointmentStatus;
use App\Scheduling\Domain\Appointment\Status\AppointmentStatuses;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;

/**
 * Stocke l'état (pattern State) par son nom et le reconstruit via la Factory du domaine.
 */
final class AppointmentStatusType extends Type
{
    public const string NAME = 'appointment_status';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 20] + $column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?AppointmentStatus
    {
        return match (true) {
            null === $value, $value instanceof AppointmentStatus => $value,
            \is_string($value) => AppointmentStatuses::fromName($value),
            default => throw InvalidType::new($value, self::class, ['null', 'string']),
        };
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return match (true) {
            null === $value, \is_string($value) => $value,
            $value instanceof AppointmentStatus => $value->name(),
            default => throw InvalidType::new($value, self::class, ['null', 'string', AppointmentStatus::class]),
        };
    }
}
