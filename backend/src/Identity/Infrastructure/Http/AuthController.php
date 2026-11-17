<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Http;

use App\Identity\Application\RegisterAccount;
use App\Identity\Infrastructure\Security\AccountUserProvider;
use App\Identity\Infrastructure\Security\SecurityUser;
use App\Shared\Application\CommandBus;
use App\Shared\Application\IdGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth', format: 'json')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commands,
        private readonly IdGenerator $ids,
    ) {
    }

    /** Création de compte, suivie de la connexion. */
    #[Route('/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterAccountInput $input,
        AccountUserProvider $users,
        Security $security,
    ): JsonResponse {
        $this->commands->dispatch(new RegisterAccount($this->ids->next(), $input->email, $input->password));
        $user = $users->loadUserByIdentifier($input->email);
        $security->login($user, 'json_login', 'main');

        return $this->json(self::view($user), Response::HTTP_CREATED);
    }

    /** Atteinte seulement après une connexion réussie par json_login (voir security.yaml). */
    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(#[CurrentUser] SecurityUser $user): JsonResponse
    {
        return $this->json(self::view($user));
    }

    #[Route('/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(#[CurrentUser] SecurityUser $user): JsonResponse
    {
        return $this->json(self::view($user));
    }

    /** Interceptée par le pare-feu (logout) : ce code n'est jamais exécuté. */
    #[Route('/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(): never
    {
        throw new \LogicException('Interceptée par le pare-feu.');
    }

    /**
     * @return array{id: string, email: string}
     */
    private static function view(SecurityUser $user): array
    {
        return ['id' => $user->id, 'email' => $user->getUserIdentifier()];
    }
}
