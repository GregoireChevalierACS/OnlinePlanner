<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Http;

use App\Identity\Application\RequestPasswordReset;
use App\Identity\Application\ResetPassword;
use App\Shared\Application\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth/password-reset', format: 'json')]
final class PasswordResetController extends AbstractController
{
    public function __construct(private readonly CommandBus $commands)
    {
    }

    /** Toujours 202, que l'adresse soit inscrite ou non : on ne révèle pas qui a un compte. */
    #[Route('', name: 'api_auth_password_reset', methods: ['POST'])]
    public function request(
        #[MapRequestPayload] RequestPasswordResetInput $input,
        Request $request,
        #[Autowire(service: 'limiter.password_reset')] RateLimiterFactoryInterface $limiter,
    ): Response {
        if (!$limiter->create($request->getClientIp())->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException(message: 'Trop de demandes. Réessayez dans quelques minutes.');
        }
        $this->commands->dispatch(new RequestPasswordReset($input->email));

        return new Response(status: Response::HTTP_ACCEPTED);
    }

    #[Route('/confirm', name: 'api_auth_password_reset_confirm', methods: ['POST'])]
    public function confirm(#[MapRequestPayload] ResetPasswordInput $input): Response
    {
        $this->commands->dispatch(new ResetPassword($input->token, $input->password));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
