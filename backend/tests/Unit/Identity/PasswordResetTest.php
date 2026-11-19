<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity;

use App\Identity\Application\AccountEmails;
use App\Identity\Application\TokenHash;
use App\Identity\Domain\Account;
use App\Identity\Domain\AccountId;
use App\Identity\Domain\InvalidLink;
use App\Identity\Domain\PasswordReset;
use PHPUnit\Framework\TestCase;

final class PasswordResetTest extends TestCase
{
    private \DateTimeImmutable $issuedAt;

    protected function setUp(): void
    {
        $this->issuedAt = new \DateTimeImmutable('2026-01-05 10:00:00');
    }

    public function testIsValidOneHourAndReturnsTheAccount(): void
    {
        $reset = PasswordReset::issue('hash', new AccountId('acc-1'), $this->issuedAt);

        self::assertSame('hash', $reset->tokenHash());
        self::assertSame('acc-1', $reset->accountId()->value);
        self::assertEquals(new \DateTimeImmutable('2026-01-05 11:00:00'), $reset->expiresAt());
        self::assertFalse($reset->isUsed());

        self::assertSame('acc-1', $reset->consume(new \DateTimeImmutable('2026-01-05 10:59:59'))->value);
        self::assertTrue($reset->isUsed());
    }

    public function testCannotBeUsedTwice(): void
    {
        $reset = PasswordReset::issue('hash', new AccountId('acc-1'), $this->issuedAt);
        $reset->consume($this->issuedAt);

        $this->expectException(InvalidLink::class);
        $this->expectExceptionMessage('Ce lien est invalide ou a expiré. Demandez-en un nouveau.');

        $reset->consume($this->issuedAt);
    }

    public function testExpiresAfterOneHour(): void
    {
        $reset = PasswordReset::issue('hash', new AccountId('acc-1'), $this->issuedAt);

        try {
            $reset->consume(new \DateTimeImmutable('2026-01-05 11:00:00'));
            self::fail('InvalidLink attendue');
        } catch (InvalidLink) {
            self::assertFalse($reset->isUsed());
        }
    }

    public function testChangePasswordReplacesTheHash(): void
    {
        $account = Account::register(new AccountId('acc-1'), 'jane@example.com', 'ancien');

        $account->changePassword('nouveau');

        self::assertSame('nouveau', $account->passwordHash());
    }

    public function testTokenHashIsSha256(): void
    {
        self::assertSame(hash('sha256', 'secret'), TokenHash::of('secret'));
        self::assertSame(64, \strlen(TokenHash::of('secret')));
    }

    public function testResetEmail(): void
    {
        $mail = AccountEmails::passwordReset('jane@example.com', 'https://app.test/mot-de-passe/nouveau?token=abc');

        self::assertSame('jane@example.com', $mail->recipient);
        self::assertSame('Réinitialisation de votre mot de passe', $mail->subject);
        self::assertSame(
            "Bonjour,\n\nPour choisir un nouveau mot de passe, ouvrez ce lien (valable 1 heure, utilisable une seule fois) :\n"
            ."https://app.test/mot-de-passe/nouveau?token=abc\n\n"
            ."Si vous n'avez rien demandé, ignorez cet e-mail : votre mot de passe actuel reste valable.\n\nÀ bientôt,\nOnlinePlanner",
            $mail->body,
        );
    }
}
