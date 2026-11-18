<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Application\ResetTokens;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsAlias(ResetTokens::class)]
final readonly class RandomResetTokens implements ResetTokens
{
    public function __construct(#[Autowire(env: 'FRONT_URL')] private string $frontUrl)
    {
    }

    public function generate(): string
    {
        // 256 bits d'aléa cryptographique : impossible à deviner.
        return bin2hex(random_bytes(32));
    }

    public function linkFor(string $token): string
    {
        return rtrim($this->frontUrl, '/').'/mot-de-passe/nouveau?'.http_build_query(['token' => $token]);
    }
}
