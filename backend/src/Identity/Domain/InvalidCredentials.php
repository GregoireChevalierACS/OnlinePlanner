<?php

declare(strict_types=1);

namespace App\Identity\Domain;

use App\Shared\Domain\DomainException;

final class InvalidCredentials extends DomainException
{
    public static function email(string $email): self
    {
        return new self(\sprintf('« %s » n\'est pas une adresse e-mail valide.', $email));
    }

    public static function passwordTooShort(int $minimum): self
    {
        return new self(\sprintf('Le mot de passe doit contenir au moins %d caractères.', $minimum));
    }

    public static function passwordTooLong(int $maximum): self
    {
        return new self(\sprintf('Le mot de passe ne peut pas dépasser %d caractères.', $maximum));
    }
}
