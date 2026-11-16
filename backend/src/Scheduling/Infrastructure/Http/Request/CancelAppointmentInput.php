<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CancelAppointmentInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 500)]
        public string $reason,
    ) {
    }
}
