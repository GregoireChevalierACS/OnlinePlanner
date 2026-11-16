<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Shared\Domain\Conflict;
use App\Shared\Domain\DomainException;
use App\Shared\Domain\NotFound;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Traduit toute erreur de l'API en « problem details » (RFC 9457) :
 * NotFound → 404, Conflict → 409 (avec motifs), autre erreur métier → 422,
 * erreurs de requête → 400 / 422 avec le détail des champs.
 * Les erreurs inattendues restent gérées par Symfony (500, sans fuite d'information en prod).
 */
#[AsEventListener(KernelEvents::EXCEPTION)]
final readonly class ProblemDetailsListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $problem = $this->problemFor($event->getThrowable());
        if (null !== $problem) {
            $event->setResponse(new JsonResponse($problem, $problem['status'], ['Content-Type' => 'application/problem+json']));
        }
    }

    /**
     * @return array{type: string, title: string, status: int, detail: string, reasons?: list<string>, violations?: list<array{field: string, message: string}>}|null
     */
    private function problemFor(\Throwable $error): ?array
    {
        return match (true) {
            $error instanceof NotFound => self::problem(Response::HTTP_NOT_FOUND, $error->getMessage()),
            $error instanceof Conflict => self::problem(Response::HTTP_CONFLICT, $error->getMessage()) + ['reasons' => $error->reasons()],
            $error instanceof DomainException => self::problem(Response::HTTP_UNPROCESSABLE_ENTITY, $error->getMessage()),
            $error instanceof HttpExceptionInterface => $this->httpProblem($error),
            default => null,
        };
    }

    /**
     * @return array{type: string, title: string, status: int, detail: string, violations?: list<array{field: string, message: string}>}
     */
    private function httpProblem(HttpExceptionInterface&\Throwable $error): array
    {
        $validation = $error->getPrevious();
        if ($validation instanceof ValidationFailedException) {
            $violations = [];
            foreach ($validation->getViolations() as $violation) {
                $violations[] = ['field' => $violation->getPropertyPath(), 'message' => (string) $violation->getMessage()];
            }

            return self::problem($error->getStatusCode(), 'La requête contient des données invalides.') + ['violations' => $violations];
        }

        return self::problem($error->getStatusCode(), $error->getMessage());
    }

    /**
     * @return array{type: string, title: string, status: int, detail: string}
     */
    private static function problem(int $status, string $detail): array
    {
        return [
            'type' => 'about:blank',
            'title' => Response::$statusTexts[$status] ?? 'Error',
            'status' => $status,
            'detail' => $detail,
        ];
    }
}
