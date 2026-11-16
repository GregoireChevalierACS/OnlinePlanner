<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class OpeningHoursInput
{
    public function __construct(
        #[Assert\Range(min: 1, max: 7)]
        public int $weekday,
        #[Assert\Regex('/^([01]\d|2[0-3]):[0-5]\d$/', message: 'Heure attendue au format HH:MM.')]
        public string $opensAt,
        #[Assert\Regex('/^([01]\d|2[0-3]):[0-5]\d$/', message: 'Heure attendue au format HH:MM.')]
        public string $closesAt,
    ) {
    }
}
