<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Http\Controller;

use App\Scheduling\Application\Command\CancelAppointment;
use App\Scheduling\Application\Command\ConfirmAppointment;
use App\Scheduling\Application\Query\GetAppointment;
use App\Scheduling\Infrastructure\Http\Request\CancelAppointmentInput;
use App\Shared\Application\CommandBus;
use App\Shared\Application\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/appointments/{appointmentId}', format: 'json')]
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
