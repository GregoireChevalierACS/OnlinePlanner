<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine;

use App\Shared\Domain\Identifier;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;

/**
 * Adapter DBAL : stocke un identifiant Value Object en VARCHAR(36).
 */
abstract class IdentifierType extends Type
{
    /**
     * @return class-string<Identifier>
     */
    abstract protected function identifierClass(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 36] + $column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Identifier
    {
        if (null === $value || $value instanceof Identifier) {
            return $value;
        }
        if (!\is_string($value)) {
            throw InvalidType::new($value, static::class, ['null', 'string']);
        }
        $class = $this->identifierClass();

        return new $class($value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return match (true) {
            null === $value, \is_string($value) => $value,
            $value instanceof Identifier => $value->value,
            default => throw InvalidType::new($value, static::class, ['null', 'string', Identifier::class]),
        };
    }
}
