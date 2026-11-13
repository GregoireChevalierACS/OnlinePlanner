<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Exception;

use App\Shared\Domain\DomainException;

final class InvalidCustomer extends DomainException
{
    public static function emptyName(): self
    {
        return new self('Le nom du client est obligatoire.');
    }
}
