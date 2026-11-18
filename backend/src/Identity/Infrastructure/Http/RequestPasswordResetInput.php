<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Http;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RequestPasswordResetInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 320)]
        public string $email,
    ) {
    }
}
