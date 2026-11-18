<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Application\Notification;

use App\Scheduling\Application\Notification\AppointmentEmails;
use App\Scheduling\Domain\Appointment\Customer;
use App\Scheduling\Domain\Appointment\Email;
use App\Scheduling\Domain\Availability\TimeSlot;
use PHPUnit\Framework\TestCase;

final class AppointmentEmailsTest extends TestCase
{
    private Customer $customer;
    private TimeSlot $slot;

    protected function setUp(): void
    {
        $this->customer = new Customer('Jane Doe', new Email('jane@example.com'));
        $this->slot = TimeSlot::startingAt(new \DateTimeImmutable('2026-01-05 09:30'), 30);
    }

    public function testBookingReceived(): void
    {
        $mail = AppointmentEmails::bookingReceived($this->customer, 'Dr Martin', $this->slot);

        self::assertSame('jane@example.com', $mail->recipient);
        self::assertSame('Demande de rendez-vous reçue – Dr Martin', $mail->subject);
        self::assertSame(
            "Bonjour Jane Doe,\n\nVotre demande de rendez-vous avec Dr Martin le 05/01/2026 de 09:30 à 10:00 a bien été reçue.\n"
            ."Vous recevrez un e-mail dès qu'elle sera confirmée.\n\nÀ bientôt,\nOnlinePlanner",
            $mail->body,
        );
    }

    public function testNewBookingForOwner(): void
    {
        $mail = AppointmentEmails::newBookingForOwner('owner@example.com', $this->customer, 'Dr Martin', $this->slot);

        self::assertSame('owner@example.com', $mail->recipient);
        self::assertSame('Nouvelle demande de rendez-vous – Dr Martin', $mail->subject);
        self::assertSame(
            "Bonjour,\n\nJane Doe (jane@example.com) demande un rendez-vous le 05/01/2026 de 09:30 à 10:00 sur l'agenda « Dr Martin ».\n"
            ."Connectez-vous pour le confirmer ou l'annuler.\n\nÀ bientôt,\nOnlinePlanner",
            $mail->body,
        );
    }

    public function testConfirmed(): void
    {
        $mail = AppointmentEmails::confirmed($this->customer, 'Dr Martin', $this->slot);

        self::assertSame('jane@example.com', $mail->recipient);
        self::assertSame('Rendez-vous confirmé – Dr Martin', $mail->subject);
        self::assertSame(
            "Bonjour Jane Doe,\n\nVotre rendez-vous avec Dr Martin le 05/01/2026 de 09:30 à 10:00 est confirmé.\n\nÀ bientôt,\nOnlinePlanner",
            $mail->body,
        );
    }

    public function testCancelledIncludesTheReason(): void
    {
        $mail = AppointmentEmails::cancelled($this->customer, 'Dr Martin', $this->slot, 'Empêchement');

        self::assertSame('jane@example.com', $mail->recipient);
        self::assertSame('Rendez-vous annulé – Dr Martin', $mail->subject);
        self::assertSame(
            "Bonjour Jane Doe,\n\nVotre rendez-vous avec Dr Martin le 05/01/2026 de 09:30 à 10:00 est annulé.\nMotif : Empêchement\n\nÀ bientôt,\nOnlinePlanner",
            $mail->body,
        );
    }
}
