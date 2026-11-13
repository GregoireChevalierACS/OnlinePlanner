<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Exception;

use App\Shared\Domain\DomainException;

final class InvalidEmail extends DomainException
{
    public static function fromValue(string $value): self
    {
        return new self(\sprintf('« %s » n\'est pas une adresse e-mail valide.', $value));
    }
}
