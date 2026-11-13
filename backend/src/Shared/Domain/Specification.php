<?php

declare(strict_types=1);

namespace App\Shared\Domain;

/**
 * Specification : une règle métier isolée, combinable et testable seule.
 *
 * @template T
 */
interface Specification
{
    /**
     * @param T $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool;
}
