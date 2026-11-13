<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Booking;

use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Exception\SlotUnavailable;
use App\Shared\Domain\Specification;

/**
 * Composite de Specifications : un créneau est réservable si toutes les règles passent.
 * Chaque règle est nommée par son motif de refus, pour pouvoir l'expliquer à l'utilisateur.
 *
 * @implements Specification<TimeSlot>
 */
final readonly class BookingPolicy implements Specification
{
    /**
     * @param array<string, Specification<TimeSlot>> $rules motif de refus => règle
     */
    public function __construct(private array $rules)
    {
    }

    /**
     * @return list<string> les motifs des règles non respectées
     */
    public function violations(TimeSlot $slot): array
    {
        $violations = [];
        foreach ($this->rules as $reason => $rule) {
            if (!$rule->isSatisfiedBy($slot)) {
                $violations[] = $reason;
            }
        }

        return $violations;
    }

    /**
     * @param TimeSlot $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return [] === $this->violations($candidate);
    }

    public function assertSatisfiedBy(TimeSlot $slot): void
    {
        $violations = $this->violations($slot);
        if ([] !== $violations) {
            throw SlotUnavailable::because($violations);
        }
    }
}
