<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity;

use App\Identity\Application\AccountEmails;
use App\Identity\Application\EmailVerificationSender;
use App\Identity\Application\ResendEmailVerification;
use App\Identity\Application\ResendEmailVerificationHandler;
use App\Identity\Application\TokenHash;
use App\Identity\Application\VerifyEmail;
use App\Identity\Application\VerifyEmailHandler;
use App\Identity\Domain\Account;
use App\Identity\Domain\AccountId;
use App\Identity\Domain\EmailVerification;
use App\Identity\Domain\InvalidLink;
use App\Identity\Infrastructure\Persistence\InMemoryAccountRepository;
use App\Identity\Infrastructure\Persistence\InMemoryEmailVerificationRepository;
use App\Tests\Double\FakeSecretLinks;
use App\Tests\Double\FixedClock;
use App\Tests\Double\RecordingNotifier;
use PHPUnit\Framework\TestCase;

final class EmailVerificationTest extends TestCase
{
    private InMemoryAccountRepository $accounts;
    private InMemoryEmailVerificationRepository $verifications;
    private RecordingNotifier $notifier;
    private FixedClock $clock;

    protected function setUp(): void
    {
        $this->accounts = new InMemoryAccountRepository();
        $this->accounts->save(Account::register(new AccountId('acc-1'), 'jane@example.com', 'hash'));
        $this->verifications = new InMemoryEmailVerificationRepository();
        $this->notifier = new RecordingNotifier();
        $this->clock = FixedClock::at('2026-01-05 10:00');
    }

    public function testLinkIsValidTwoDays(): void
    {
        $verification = EmailVerification::issue('hash', new AccountId('acc-1'), new \DateTimeImmutable('2026-01-05 10:00'));

        self::assertEquals(new \DateTimeImmutable('2026-01-07 10:00'), $verification->expiresAt());
    }

    public function testVerificationIsRecordedOnce(): void
    {
        $account = Account::register(new AccountId('acc-1'), 'jane@example.com', 'hash');
        self::assertFalse($account->isEmailVerified());

        $account->verifyEmail(new \DateTimeImmutable('2026-01-05'));
        $account->verifyEmail(new \DateTimeImmutable('2026-02-01'));

        self::assertTrue($account->isEmailVerified());
    }

    public function testValidLinkVerifiesTheAccountOnce(): void
    {
        $this->verifications->save(EmailVerification::issue(TokenHash::of('jeton'), new AccountId('acc-1'), $this->clock->now()));

        $this->verifyHandler()(new VerifyEmail('jeton'));

        self::assertTrue($this->accounts->find(new AccountId('acc-1'))?->isEmailVerified());
        self::assertTrue($this->verifications->findByTokenHash(TokenHash::of('jeton'))?->isUsed());

        $this->expectException(InvalidLink::class);
        $this->verifyHandler()(new VerifyEmail('jeton'));
    }

    public function testUnknownLinkIsRejected(): void
    {
        $this->expectException(InvalidLink::class);

        $this->verifyHandler()(new VerifyEmail('inconnu'));
    }

    public function testLinkOfADeletedAccountIsRejected(): void
    {
        $this->verifications->save(EmailVerification::issue(TokenHash::of('jeton'), new AccountId('disparu'), $this->clock->now()));

        $this->expectException(InvalidLink::class);

        $this->verifyHandler()(new VerifyEmail('jeton'));
    }

    public function testResendSendsANewLinkToAnUnverifiedAccount(): void
    {
        $this->resendHandler()(new ResendEmailVerification('acc-1'));

        self::assertCount(1, $this->notifier->sent);
        self::assertSame('Confirmez votre adresse e-mail', $this->notifier->sent[0]->subject);
        self::assertNotNull($this->verifications->findByTokenHash(TokenHash::of('jeton-1')));
    }

    public function testResendDoesNothingForAVerifiedOrUnknownAccount(): void
    {
        $account = $this->accounts->find(new AccountId('acc-1'));
        self::assertNotNull($account);
        $account->verifyEmail($this->clock->now());
        $this->accounts->save($account);

        $this->resendHandler()(new ResendEmailVerification('acc-1'));
        $this->resendHandler()(new ResendEmailVerification('inconnu'));

        self::assertSame([], $this->notifier->sent);
    }

    public function testVerificationEmail(): void
    {
        $mail = AccountEmails::emailVerification('jane@example.com', 'https://app.test/verifier-email?token=abc');

        self::assertSame('jane@example.com', $mail->recipient);
        self::assertSame('Confirmez votre adresse e-mail', $mail->subject);
        self::assertSame(
            "Bonjour,\n\nPour confirmer que cette adresse est bien la vôtre, ouvrez ce lien (valable 2 jours) :\n"
            ."https://app.test/verifier-email?token=abc\n\n"
            ."Tant que l'adresse n'est pas confirmée, vous ne recevez pas les e-mails de nouvelles réservations.\n"
            ."Si vous n'avez pas créé de compte, ignorez cet e-mail.\n\nÀ bientôt,\nOnlinePlanner",
            $mail->body,
        );
    }

    private function verifyHandler(): VerifyEmailHandler
    {
        return new VerifyEmailHandler($this->verifications, $this->accounts, $this->clock);
    }

    private function resendHandler(): ResendEmailVerificationHandler
    {
        return new ResendEmailVerificationHandler(
            $this->accounts,
            new EmailVerificationSender($this->verifications, new FakeSecretLinks(), $this->notifier, $this->clock),
        );
    }
}
