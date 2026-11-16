<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Http\Controller;

use App\Scheduling\Application\Command\BookAppointment;
use App\Scheduling\Application\Command\RegisterProvider;
use App\Scheduling\Application\Query\ListAppointments;
use App\Scheduling\Application\Query\ListAvailableSlots;
use App\Scheduling\Infrastructure\Http\Request\BookAppointmentInput;
use App\Scheduling\Infrastructure\Http\Request\DayQuery;
use App\Scheduling\Infrastructure\Http\Request\PeriodQuery;
use App\Scheduling\Infrastructure\Http\Request\RegisterProviderInput;
use App\Shared\Application\CommandBus;
use App\Shared\Application\IdGenerator;
use App\Shared\Application\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur fin : traduit HTTP en commandes / requêtes, sans aucune logique métier.
 */
#[Route('/api/providers', format: 'json')]
final class ProviderController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commands,
        private readonly QueryBus $queries,
        private readonly IdGenerator $ids,
    ) {
    }

    #[Route('', name: 'provider_register', methods: ['POST'])]
    public function register(#[MapRequestPayload] RegisterProviderInput $input): JsonResponse
    {
        $id = $this->ids->next();
        $this->commands->dispatch(new RegisterProvider(
            $id,
            $input->name,
            $input->openingHoursAsArrays(),
            $input->slotDurationMinutes,
            $input->bufferMinutes,
            $input->minimumNoticeMinutes,
        ));

        return $this->json(['id' => $id], Response::HTTP_CREATED);
    }

    #[Route('/{providerId}/slots', name: 'provider_slots', methods: ['GET'])]
    public function slots(string $providerId, #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] DayQuery $query): JsonResponse
    {
        return $this->json($this->queries->ask(new ListAvailableSlots($providerId, $query->day())));
    }

    #[Route('/{providerId}/appointments', name: 'provider_book', methods: ['POST'])]
    public function book(string $providerId, #[MapRequestPayload] BookAppointmentInput $input): JsonResponse
    {
        $id = $this->ids->next();
        $this->commands->dispatch(new BookAppointment($id, $providerId, $input->startsAt, $input->customerName, $input->customerEmail));

        return $this->json(['id' => $id], Response::HTTP_CREATED, [
            'Location' => $this->generateUrl('appointment_show', ['appointmentId' => $id]),
        ]);
    }

    #[Route('/{providerId}/appointments', name: 'provider_agenda', methods: ['GET'])]
    public function agenda(string $providerId, #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] PeriodQuery $query): JsonResponse
    {
        return $this->json($this->queries->ask(new ListAppointments($providerId, $query->fromDay(), $query->toDay())));
    }
}
