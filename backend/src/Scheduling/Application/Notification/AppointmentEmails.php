<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Notification;

use App\Scheduling\Domain\Appointment\Customer;
use App\Scheduling\Domain\Availability\TimeSlot;
use App\Shared\Application\Notification\Notification;

/**
 * Factory : le texte de chaque e-mail, en un seul endroit, testable sans serveur d'e-mail.
 */
final class AppointmentEmails
{
    private const string SIGNATURE = "\n\nÀ bientôt,\nOnlinePlanner";

    public static function bookingReceived(Customer $customer, string $providerName, TimeSlot $slot): Notification
    {
        return new Notification(
            $customer->email->value,
            \sprintf('Demande de rendez-vous reçue – %s', $providerName),
            \sprintf(
                "Bonjour %s,\n\nVotre demande de rendez-vous avec %s %s a bien été reçue.\nVous recevrez un e-mail dès qu'elle sera confirmée.",
                $customer->name,
                $providerName,
                self::when($slot),
            ).self::SIGNATURE,
        );
    }

    public static function newBookingForOwner(string $ownerEmail, Customer $customer, string $providerName, TimeSlot $slot): Notification
    {
        return new Notification(
            $ownerEmail,
            \sprintf('Nouvelle demande de rendez-vous – %s', $providerName),
            \sprintf(
                "Bonjour,\n\n%s (%s) demande un rendez-vous %s sur l'agenda « %s ».\nConnectez-vous pour le confirmer ou l'annuler.",
                $customer->name,
                $customer->email->value,
                self::when($slot),
                $providerName,
            ).self::SIGNATURE,
        );
    }

    public static function confirmed(Customer $customer, string $providerName, TimeSlot $slot): Notification
    {
        return new Notification(
            $customer->email->value,
            \sprintf('Rendez-vous confirmé – %s', $providerName),
            \sprintf(
                "Bonjour %s,\n\nVotre rendez-vous avec %s %s est confirmé.",
                $customer->name,
                $providerName,
                self::when($slot),
            ).self::SIGNATURE,
        );
    }

    public static function cancelled(Customer $customer, string $providerName, TimeSlot $slot, string $reason): Notification
    {
        return new Notification(
            $customer->email->value,
            \sprintf('Rendez-vous annulé – %s', $providerName),
            \sprintf(
                "Bonjour %s,\n\nVotre rendez-vous avec %s %s est annulé.\nMotif : %s",
                $customer->name,
                $providerName,
                self::when($slot),
                $reason,
            ).self::SIGNATURE,
        );
    }

    private static function when(TimeSlot $slot): string
    {
        return \sprintf('le %s de %s à %s', $slot->start->format('d/m/Y'), $slot->start->format('H:i'), $slot->end->format('H:i'));
    }
}
