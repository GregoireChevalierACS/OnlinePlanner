<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Query;

use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\ProviderId;
use App\Shared\Application\QueryHandler;

final readonly class ListAppointmentsHandler implements QueryHandler
{
    public function __construct(private AppointmentRepository $appointments)
    {
    }

    /**
     * @return list<AppointmentView>
     */
    public function __invoke(ListAppointments $query): array
    {
        $period = TimeSlot::between($query->from->setTime(0, 0), $query->to->setTime(0, 0)->modify('+1 day'));

        return array_map(
            AppointmentView::fromAppointment(...),
            $this->appointments->overlapping(new ProviderId($query->providerId), $period),
        );
    }
}
