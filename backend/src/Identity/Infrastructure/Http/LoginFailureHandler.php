<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Échec de connexion au format problem+json. Message unique pour un e-mail inconnu
 * ou un mauvais mot de passe : on ne révèle pas quelles adresses sont inscrites.
 */
final readonly class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        [$status, $detail] = $exception instanceof TooManyLoginAttemptsAuthenticationException
            ? [Response::HTTP_TOO_MANY_REQUESTS, 'Trop de tentatives de connexion. Réessayez dans quelques minutes.']
            : [Response::HTTP_UNAUTHORIZED, 'Adresse e-mail ou mot de passe incorrect.'];

        return new JsonResponse(
            ['type' => 'about:blank', 'title' => Response::$statusTexts[$status], 'status' => $status, 'detail' => $detail],
            $status,
            ['Content-Type' => 'application/problem+json'],
        );
    }
}
