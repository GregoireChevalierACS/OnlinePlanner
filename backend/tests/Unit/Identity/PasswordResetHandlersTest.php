<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity;

use App\Identity\Application\PasswordHasher;
use App\Identity\Application\RequestPasswordReset;
use App\Identity\Application\RequestPasswordResetHandler;
use App\Identity\Application\ResetPassword;
use App\Identity\Application\ResetPasswordHandler;
use App\Identity\Application\ResetTokens;
use App\Identity\Application\TokenHash;
use App\Identity\Domain\Account;
use App\Identity\Domain\AccountId;
use App\Identity\Domain\InvalidCredentials;
use App\Identity\Domain\InvalidResetToken;
use App\Identity\Domain\PasswordReset;
use App\Identity\Domain\PlainPassword;
use App\Identity\Infrastructure\Persistence\InMemoryAccountRepository;
use App\Identity\Infrastructure\Persistence\InMemoryPasswordResetRepository;
use App\Tests\Double\FixedClock;
use App\Tests\Double\RecordingNotifier;
use PHPUnit\Framework\TestCase;

final class PasswordResetHandlersTest extends TestCase
{
    private const string TOKEN = 'jeton-secret';

    private InMemoryAccountRepository $accounts;
    private InMemoryPasswordResetRepository $resets;
    private RecordingNotifier $notifier;
    private FixedClock $clock;

    protected function setUp(): void
    {
        $this->accounts = new InMemoryAccountRepository();
        $this->accounts->save(Account::register(new AccountId('acc-1'), 'jane@example.com', 'hash:ancien mot de passe'));
        $this->resets = new InMemoryPasswordResetRepository();
        $this->notifier = new RecordingNotifier();
        $this->clock = FixedClock::at('2026-01-05 10:00');
    }

    public function testRequestStoresOnlyTheHashAndEmailsTheLink(): void
    {
        $this->requestHandler()(new RequestPasswordReset(' JANE@example.com '));

        $reset = $this->resets->findByTokenHash(TokenHash::of(self::TOKEN));
        self::assertNotNull($reset);
        self::assertSame('acc-1', $reset->accountId()->value);
        self::assertEquals(new \DateTimeImmutable('2026-01-05 11:00'), $reset->expiresAt());
        self::assertNull($this->resets->findByTokenHash(self::TOKEN));

        self::assertCount(1, $this->notifier->sent);
        self::assertSame('jane@example.com', $this->notifier->sent[0]->recipient);
        self::assertStringContainsString('https://app.test/reset?t='.self::TOKEN, $this->notifier->sent[0]->body);
    }

    public function testRequestForUnknownOrInvalidEmailDoesNothing(): void
    {
        $this->requestHandler()(new RequestPasswordReset('inconnu@example.com'));
        $this->requestHandler()(new RequestPasswordReset('pas-un-mail'));

        self::assertSame([], $this->notifier->sent);
        self::assertNull($this->resets->findByTokenHash(TokenHash::of(self::TOKEN)));
    }

    public function testResetChangesThePasswordOnce(): void
    {
        $this->resets->save(PasswordReset::issue(TokenHash::of(self::TOKEN), new AccountId('acc-1'), $this->clock->now()));

        $this->resetHandler()(new ResetPassword(self::TOKEN, 'nouveau mot de passe'));

        self::assertSame('hash:nouveau mot de passe', $this->accounts->findByEmail('jane@example.com')?->passwordHash());
        self::assertTrue($this->resets->findByTokenHash(TokenHash::of(self::TOKEN))?->isUsed());

        $this->expectException(InvalidResetToken::class);
        $this->resetHandler()(new ResetPassword(self::TOKEN, 'encore un autre'));
    }

    public function testUnknownTokenIsRejected(): void
    {
        $this->expectException(InvalidResetToken::class);

        $this->resetHandler()(new ResetPassword('inconnu', 'nouveau mot de passe'));
    }

    public function testTokenOfADeletedAccountIsRejected(): void
    {
        $this->resets->save(PasswordReset::issue(TokenHash::of(self::TOKEN), new AccountId('disparu'), $this->clock->now()));

        $this->expectException(InvalidResetToken::class);

        $this->resetHandler()(new ResetPassword(self::TOKEN, 'nouveau mot de passe'));
    }

    public function testWeakPasswordIsRejectedWithoutConsumingTheToken(): void
    {
        $this->resets->save(PasswordReset::issue(TokenHash::of(self::TOKEN), new AccountId('acc-1'), $this->clock->now()));

        try {
            $this->resetHandler()(new ResetPassword(self::TOKEN, 'court'));
            self::fail('InvalidCredentials attendue');
        } catch (InvalidCredentials) {
            self::assertFalse($this->resets->findByTokenHash(TokenHash::of(self::TOKEN))?->isUsed());
        }
    }

    private function requestHandler(): RequestPasswordResetHandler
    {
        $tokens = new class(self::TOKEN) implements ResetTokens {
            public function __construct(private readonly string $token)
            {
            }

            public function generate(): string
            {
                return $this->token;
            }

            public function linkFor(string $token): string
            {
                return 'https://app.test/reset?t='.$token;
            }
        };

        return new RequestPasswordResetHandler($this->accounts, $this->resets, $tokens, $this->notifier, $this->clock);
    }

    private function resetHandler(): ResetPasswordHandler
    {
        $hasher = new class implements PasswordHasher {
            public function hash(PlainPassword $password): string
            {
                return 'hash:'.$password->value;
            }
        };

        return new ResetPasswordHandler($this->resets, $this->accounts, $hasher, $this->clock);
    }
}
