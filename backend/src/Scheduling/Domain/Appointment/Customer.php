<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment;

use App\Scheduling\Domain\Exception\InvalidCustomer;

final readonly class Customer
{
    public string $name;

    public function __construct(string $name, public Email $email)
    {
        $trimmed = trim($name);
        if ('' === $trimmed) {
            throw InvalidCustomer::emptyName();
        }
        $this->name = $trimmed;
    }
}
