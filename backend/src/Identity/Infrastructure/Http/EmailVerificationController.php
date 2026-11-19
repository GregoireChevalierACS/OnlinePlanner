<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Http;

use App\Identity\Application\ResendEmailVerification;
use App\Identity\Application\VerifyEmail;
use App\Identity\Infrastructure\Security\SecurityUser;
use App\Shared\Application\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth/verify-email', format: 'json')]
final class EmailVerificationController extends AbstractController
{
    public function __construct(private readonly CommandBus $commands)
    {
    }

    /** Public : le lien est ouvert depuis l'e-mail, éventuellement sur un autre appareil. */
    #[Route('', name: 'api_auth_verify_email', methods: ['POST'])]
    public function verify(#[MapRequestPayload] VerifyEmailInput $input): Response
    {
        $this->commands->dispatch(new VerifyEmail($input->token));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/resend', name: 'api_auth_verify_email_resend', methods: ['POST'])]
    public function resend(
        #[CurrentUser] SecurityUser $user,
        #[Autowire(service: 'limiter.email_verification')] RateLimiterFactoryInterface $limiter,
    ): Response {
        if (!$limiter->create($user->id)->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException(message: 'Trop de demandes. Réessayez dans quelques minutes.');
        }
        $this->commands->dispatch(new ResendEmailVerification($user->id));

        return new Response(status: Response::HTTP_ACCEPTED);
    }
}
