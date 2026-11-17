<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Http;

use App\Identity\Domain\PlainPassword;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterAccountInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 320)]
        public string $email,
        #[Assert\Length(min: PlainPassword::MIN_LENGTH, max: PlainPassword::MAX_LENGTH)]
        #[\SensitiveParameter]
        public string $password,
    ) {
    }
}
