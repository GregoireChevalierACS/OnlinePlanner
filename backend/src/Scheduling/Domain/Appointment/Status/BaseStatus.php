<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Appointment\Status;

use App\Scheduling\Domain\Exception\InvalidStatusTransition;

/**
 * Par défaut, toute transition est interdite : chaque état n'ouvre que les siennes.
 */
abstract readonly class BaseStatus implements AppointmentStatus
{
    public function confirm(): AppointmentStatus
    {
        throw InvalidStatusTransition::from($this->name(), 'confirmer');
    }

    public function cancel(): AppointmentStatus
    {
        throw InvalidStatusTransition::from($this->name(), 'annuler');
    }

    public function complete(): AppointmentStatus
    {
        throw InvalidStatusTransition::from($this->name(), 'terminer');
    }

    public function blocksSlot(): bool
    {
        return true;
    }
}
