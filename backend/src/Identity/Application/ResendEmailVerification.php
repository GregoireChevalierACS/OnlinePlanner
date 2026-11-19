<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Shared\Application\Command;

final readonly class ResendEmailVerification implements Command
{
    public function __construct(public string $accountId)
    {
    }
}
