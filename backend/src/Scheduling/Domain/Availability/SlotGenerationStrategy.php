<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Availability;

/**
 * Strategy : façon de découper une fenêtre d'ouverture en créneaux réservables.
 * Chaque prestataire peut avoir sa propre stratégie sans toucher au calcul de disponibilité.
 */
interface SlotGenerationStrategy
{
    /**
     * @return list<TimeSlot>
     */
    public function generate(TimeSlot $window): array;
}
