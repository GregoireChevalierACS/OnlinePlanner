<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Identity\Application\SecretLinks;

/**
 * Jetons prévisibles (« jeton-1 », « jeton-2 »…) et liens lisibles, pour les tests.
 */
final class FakeSecretLinks implements SecretLinks
{
    private int $issued = 0;

    public function generate(): string
    {
        return 'jeton-'.++$this->issued;
    }

    public function passwordResetLink(string $token): string
    {
        return 'https://app.test/reset?t='.$token;
    }

    public function emailVerificationLink(string $token): string
    {
        return 'https://app.test/verify?t='.$token;
    }
}
