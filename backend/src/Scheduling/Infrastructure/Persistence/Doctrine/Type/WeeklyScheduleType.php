<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Persistence\Doctrine\Type;

use App\Scheduling\Domain\Availability\OpeningHours;
use App\Scheduling\Domain\Availability\WeeklySchedule;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

/**
 * Stocke les horaires hebdomadaires en JSON : [{"weekday":1,"opensAt":"09:00","closesAt":"12:00"}, …].
 */
final class WeeklyScheduleType extends Type
{
    public const string NAME = 'weekly_schedule';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?WeeklySchedule
    {
        if (null === $value || $value instanceof WeeklySchedule) {
            return $value;
        }
        if (!\is_string($value)) {
            throw InvalidType::new($value, self::class, ['null', 'string']);
        }

        try {
            $rows = json_decode($value, true, 3, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ValueNotConvertible::new($value, self::class, $e->getMessage(), $e);
        }
        if (!\is_array($rows)) {
            throw ValueNotConvertible::new($value, self::class, 'liste attendue');
        }

        return new WeeklySchedule(...array_map(
            static function (mixed $row) use ($value): OpeningHours {
                if (!\is_array($row) || !\is_int($row['weekday'] ?? null) || !\is_string($row['opensAt'] ?? null) || !\is_string($row['closesAt'] ?? null)) {
                    throw ValueNotConvertible::new($value, self::class, 'plage d\'ouverture invalide');
                }

                return OpeningHours::on($row['weekday'], $row['opensAt'], $row['closesAt']);
            },
            array_values($rows),
        ));
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }
        if (!$value instanceof WeeklySchedule) {
            throw InvalidType::new($value, self::class, ['null', WeeklySchedule::class]);
        }

        return json_encode(array_map(
            static fn (OpeningHours $hours): array => [
                'weekday' => $hours->weekday,
                'opensAt' => $hours->opensAt(),
                'closesAt' => $hours->closesAt(),
            ],
            $value->openingHours,
        ), \JSON_THROW_ON_ERROR);
    }
}
