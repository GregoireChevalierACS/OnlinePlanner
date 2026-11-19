<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity;

use App\Identity\Application\EmailVerificationSender;
use App\Identity\Application\PasswordHasher;
use App\Identity\Application\RegisterAccount;
use App\Identity\Application\RegisterAccountHandler;
use App\Identity\Application\TokenHash;
use App\Identity\Domain\EmailAlreadyUsed;
use App\Identity\Domain\InvalidCredentials;
use App\Identity\Domain\PlainPassword;
use App\Identity\Infrastructure\Persistence\InMemoryAccountRepository;
use App\Identity\Infrastructure\Persistence\InMemoryEmailVerificationRepository;
use App\Tests\Double\FakeSecretLinks;
use App\Tests\Double\FixedClock;
use App\Tests\Double\RecordingNotifier;
use PHPUnit\Framework\TestCase;

final class RegisterAccountHandlerTest extends TestCase
{
    private InMemoryAccountRepository $accounts;
    private InMemoryEmailVerificationRepository $verifications;
    private RecordingNotifier $notifier;
    private RegisterAccountHandler $handler;

    protected function setUp(): void
    {
        $this->accounts = new InMemoryAccountRepository();
        $this->verifications = new InMemoryEmailVerificationRepository();
        $this->notifier = new RecordingNotifier();
        $hasher = new class implements PasswordHasher {
            public function hash(PlainPassword $password): string
            {
                return 'hashed:'.$password->value;
            }
        };
        $sender = new EmailVerificationSender($this->verifications, new FakeSecretLinks(), $this->notifier, FixedClock::at('2026-01-05 10:00'));
        $this->handler = new RegisterAccountHandler($this->accounts, $hasher, $sender);
    }

    public function testStoresAnUnverifiedAccountAndSendsTheVerificationLink(): void
    {
        ($this->handler)(new RegisterAccount('acc-1', 'Jane@Example.com', 'correct horse battery'));

        $account = $this->accounts->findByEmail('jane@example.com');
        self::assertNotNull($account);
        self::assertSame('acc-1', $account->id()->value);
        self::assertSame('hashed:correct horse battery', $account->passwordHash());
        self::assertFalse($account->isEmailVerified());

        $verification = $this->verifications->findByTokenHash(TokenHash::of('jeton-1'));
        self::assertNotNull($verification);
        self::assertSame('acc-1', $verification->accountId()->value);
        self::assertEquals(new \DateTimeImmutable('2026-01-07 10:00'), $verification->expiresAt());
        self::assertCount(1, $this->notifier->sent);
        self::assertSame('jane@example.com', $this->notifier->sent[0]->recipient);
        self::assertStringContainsString('https://app.test/verify?t=jeton-1', $this->notifier->sent[0]->body);
    }

    public function testRefusesAnEmailAlreadyRegisteredWhateverItsCase(): void
    {
        ($this->handler)(new RegisterAccount('acc-1', 'jane@example.com', 'correct horse battery'));

        $this->expectException(EmailAlreadyUsed::class);

        ($this->handler)(new RegisterAccount('acc-2', 'JANE@example.com', 'another long password'));
    }

    public function testValidatesThePasswordBeforeAnything(): void
    {
        try {
            ($this->handler)(new RegisterAccount('acc-1', 'jane@example.com', 'court'));
            self::fail('InvalidCredentials attendue');
        } catch (InvalidCredentials) {
            self::assertNull($this->accounts->findByEmail('jane@example.com'));
            self::assertSame([], $this->notifier->sent);
        }
    }
}
