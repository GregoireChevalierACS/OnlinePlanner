<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Scheduling\Domain\Appointment\Appointment;
use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\Customer;
use App\Scheduling\Domain\Appointment\Email;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Scheduling\Domain\Booking\BookingPolicy;
use App\Scheduling\Domain\ProviderId;

final class AppointmentMother
{
    public static function booked(string $id, string $start, string $providerId = 'p1', int $minutes = 30): Appointment
    {
        $appointment = Appointment::book(
            new AppointmentId($id),
            new ProviderId($providerId),
            new Customer('Jane Doe', new Email('jane@example.com')),
            TimeSlot::startingAt(new \DateTimeImmutable($start), $minutes),
            new BookingPolicy([]),
            new \DateTimeImmutable('2026-01-01 08:00'),
        );
        $appointment->pullDomainEvents();

        return $appointment;
    }
}
