<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Application\SecretLinks;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsAlias(SecretLinks::class)]
final readonly class RandomSecretLinks implements SecretLinks
{
    public function __construct(#[Autowire(env: 'FRONT_URL')] private string $frontUrl)
    {
    }

    public function generate(): string
    {
        // 256 bits d'aléa cryptographique : impossible à deviner.
        return bin2hex(random_bytes(32));
    }

    public function passwordResetLink(string $token): string
    {
        return $this->link('/mot-de-passe/nouveau', $token);
    }

    public function emailVerificationLink(string $token): string
    {
        return $this->link('/verifier-email', $token);
    }

    private function link(string $path, string $token): string
    {
        return rtrim($this->frontUrl, '/').$path.'?'.http_build_query(['token' => $token]);
    }
}
