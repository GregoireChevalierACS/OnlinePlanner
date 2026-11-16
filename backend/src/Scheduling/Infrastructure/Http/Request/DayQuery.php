<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class DayQuery
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $date,
    ) {
    }

    public function day(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->date);
    }
}
