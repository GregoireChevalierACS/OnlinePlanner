<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Exception;

use App\Shared\Domain\DomainException;

final class InvalidOpeningHours extends DomainException
{
    public static function weekday(int $weekday): self
    {
        return new self(\sprintf('Jour de semaine invalide : %d (attendu 1 = lundi … 7 = dimanche).', $weekday));
    }

    public static function time(string $time): self
    {
        return new self(\sprintf('Heure invalide : « %s » (attendu HH:MM).', $time));
    }

    public static function closesBeforeOpening(): self
    {
        return new self('L\'heure de fermeture doit être postérieure à l\'heure d\'ouverture.');
    }

    public static function overlapping(int $weekday): self
    {
        return new self(\sprintf('Plages d\'ouverture qui se chevauchent le jour %d.', $weekday));
    }
}
