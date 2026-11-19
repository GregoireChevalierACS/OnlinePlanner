<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Http;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VerifyEmailInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 128)]
        #[\SensitiveParameter]
        public string $token,
    ) {
    }
}
