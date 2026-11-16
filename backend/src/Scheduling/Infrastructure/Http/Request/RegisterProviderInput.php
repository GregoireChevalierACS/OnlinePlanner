<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO HTTP : valide la forme de la requête. Les règles métier restent dans le domaine.
 */
final readonly class RegisterProviderInput
{
    /**
     * @param list<OpeningHoursInput> $openingHours
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,
        #[Assert\Valid]
        public array $openingHours,
        #[Assert\Range(min: 5, max: 480)]
        public int $slotDurationMinutes,
        #[Assert\Range(min: 0, max: 240)]
        public int $bufferMinutes = 0,
        #[Assert\Range(min: 0, max: 43200)]
        public int $minimumNoticeMinutes = 0,
    ) {
    }

    /**
     * @return list<array{weekday: int, opensAt: string, closesAt: string}>
     */
    public function openingHoursAsArrays(): array
    {
        return array_map(
            static fn (OpeningHoursInput $h): array => ['weekday' => $h->weekday, 'opensAt' => $h->opensAt, 'closesAt' => $h->closesAt],
            $this->openingHours,
        );
    }
}
