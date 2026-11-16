<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class BookAppointmentInput
{
    public function __construct(
        public \DateTimeImmutable $startsAt,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $customerName,
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 320)]
        public string $customerEmail,
    ) {
    }
}
