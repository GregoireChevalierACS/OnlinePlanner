<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Http;

use App\Identity\Domain\PlainPassword;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 128)]
        #[\SensitiveParameter]
        public string $token,
        #[Assert\Length(min: PlainPassword::MIN_LENGTH, max: PlainPassword::MAX_LENGTH)]
        #[\SensitiveParameter]
        public string $password,
    ) {
    }
}
