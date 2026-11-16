<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PeriodQuery
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $from,
        #[Assert\NotBlank]
        #[Assert\Date]
        #[Assert\GreaterThanOrEqual(propertyPath: 'from', message: 'La date de fin doit suivre la date de début.')]
        public string $to,
    ) {
    }

    public function fromDay(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->from);
    }

    public function toDay(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->to);
    }
}
