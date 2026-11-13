<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment;

use App\Scheduling\Domain\Exception\InvalidEmail;

final readonly class Email implements \Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (false === filter_var($normalized, \FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmail::fromValue($value);
        }
        $this->value = $normalized;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
