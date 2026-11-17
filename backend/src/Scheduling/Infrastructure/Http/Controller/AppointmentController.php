<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Http\Controller;

use App\Scheduling\Application\Command\CancelAppointment;
use App\Scheduling\Application\Command\ConfirmAppointment;
use App\Scheduling\Application\Query\GetAppointment;
use App\Scheduling\Infrastructure\Http\Request\CancelAppointmentInput;
use App\Scheduling\Infrastructure\Security\ScheduleAccessVoter;
use App\Shared\Application\CommandBus;
use App\Shared\Application\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Détail, confirmation et annulation : réservés au propriétaire de l'agenda du rendez-vous.
 */
#[Route('/api/appointments/{appointmentId}', format: 'json')]
#[IsGranted(ScheduleAccessVoter::MANAGE_APPOINTMENT, subject: 'appointmentId')]
final class AppointmentController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commands,
        private readonly QueryBus $queries,
    ) {
    }

    #[Route('', name: 'appointment_show', methods: ['GET'])]
    public function show(string $appointmentId): JsonResponse
    {
        return $this->json($this->queries->ask(new GetAppointment($appointmentId)));
    }

    #[Route('/confirm', name: 'appointment_confirm', methods: ['POST'])]
    public function confirm(string $appointmentId): Response
    {
        $this->commands->dispatch(new ConfirmAppointment($appointmentId));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/cancel', name: 'appointment_cancel', methods: ['POST'])]
    public function cancel(string $appointmentId, #[MapRequestPayload] CancelAppointmentInput $input): Response
    {
        $this->commands->dispatch(new CancelAppointment($appointmentId, $input->reason));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
