<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Shared\Application\Command;

final readonly class RequestPasswordReset implements Command
{
    public function __construct(public string $email)
    {
    }
}
