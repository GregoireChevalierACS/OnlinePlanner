<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Identity\Domain\PlainPassword;

/**
 * Port de hachage : l'algorithme (et son coût) relève de l'infrastructure.
 */
interface PasswordHasher
{
    public function hash(PlainPassword $password): string;
}
