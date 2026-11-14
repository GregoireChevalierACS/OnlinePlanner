<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Query;

use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Shared\Application\QueryHandler;

final readonly class GetAppointmentHandler implements QueryHandler
{
    public function __construct(private AppointmentRepository $appointments)
    {
    }

    public function __invoke(GetAppointment $query): AppointmentView
    {
        return AppointmentView::fromAppointment($this->appointments->get(new AppointmentId($query->appointmentId)));
    }
}
