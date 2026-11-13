<?php

declare(strict_types=1);

namespace App\Shared\Domain;

/**
 * Racine de toutes les erreurs métier : la couche HTTP les traduit en 4xx.
 */
abstract class DomainException extends \DomainException
{
}
