<?php

declare(strict_types=1);

namespace App\Identity\Domain;

/**
 * Value Object : mot de passe en clair, le temps de le hacher. Jamais persisté ni affiché.
 */
final readonly class PlainPassword
{
    public const int MIN_LENGTH = 12;
    public const int MAX_LENGTH = 4096;

    public function __construct(#[\SensitiveParameter] public string $value)
    {
        $length = mb_strlen($value);
        if ($length < self::MIN_LENGTH) {
            throw InvalidCredentials::passwordTooShort(self::MIN_LENGTH);
        }
        if ($length > self::MAX_LENGTH) {
            throw InvalidCredentials::passwordTooLong(self::MAX_LENGTH);
        }
    }

    public function __debugInfo(): array
    {
        return ['value' => '***'];
    }
}
