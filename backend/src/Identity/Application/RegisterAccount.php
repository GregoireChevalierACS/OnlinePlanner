<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Shared\Application\Command;

final readonly class RegisterAccount implements Command
{
    public function __construct(
        public string $accountId,
        public string $email,
        #[\SensitiveParameter] public string $password,
    ) {
    }
}
