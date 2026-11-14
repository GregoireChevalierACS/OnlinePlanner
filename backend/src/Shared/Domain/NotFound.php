<?php

declare(strict_types=1);

namespace App\Shared\Domain;

/**
 * Ressource métier introuvable : la couche HTTP la traduit en 404.
 */
abstract class NotFound extends DomainException
{
}
