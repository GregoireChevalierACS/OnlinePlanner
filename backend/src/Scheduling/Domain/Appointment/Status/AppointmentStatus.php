<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment\Status;

/**
 * State : chaque statut décide lui-même des transitions autorisées,
 * ce qui supprime les `switch` sur le statut dans l'agrégat.
 *
 *   pending ──confirm──▶ confirmed ──complete──▶ completed
 *      │                    │
 *      └──────cancel────────┴──────▶ cancelled
 */
interface AppointmentStatus
{
    public function name(): string;

    public function confirm(): self;

    public function cancel(): self;

    public function complete(): self;

    /** Le créneau reste-t-il occupé dans l'agenda ? */
    public function blocksSlot(): bool;
}
