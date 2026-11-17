<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity;

use App\Identity\Domain\Account;
use App\Identity\Domain\AccountId;
use App\Identity\Domain\EmailAlreadyUsed;
use App\Identity\Domain\InvalidCredentials;
use App\Identity\Domain\PlainPassword;
use App\Shared\Domain\Conflict;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    public function testRegisterNormalizesTheEmailAndKeepsOnlyTheHash(): void
    {
        $account = Account::register(new AccountId('acc-1'), '  Jane.Doe@Example.COM ', 'hash');

        self::assertSame('acc-1', $account->id()->value);
        self::assertSame('jane.doe@example.com', $account->email());
        self::assertSame('hash', $account->passwordHash());
    }

    public function testLoginAndRegistrationShareTheSameCanonicalForm(): void
    {
        self::assertSame(Account::normalize(' JANE@example.COM'), Account::normalize('jane@EXAMPLE.com '));
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->expectException(InvalidCredentials::class);
        $this->expectExceptionMessage('« pas-un-mail » n\'est pas une adresse e-mail valide.');

        Account::normalize('pas-un-mail');
    }

    #[DataProvider('validPasswords')]
    public function testAcceptsPasswordWithinBounds(string $password): void
    {
        self::assertSame($password, (new PlainPassword($password))->value);
    }

    /** @return iterable<string, array{string}> */
    public static function validPasswords(): iterable
    {
        yield 'minimum (12)' => [str_repeat('a', 12)];
        yield 'maximum (4096)' => [str_repeat('a', 4096)];
        yield 'caractères multioctets comptés une fois' => [str_repeat('é', 12)];
    }

    #[DataProvider('shortPasswords')]
    public function testRejectsShortPassword(string $password): void
    {
        $this->expectException(InvalidCredentials::class);
        $this->expectExceptionMessage('Le mot de passe doit contenir au moins 12 caractères.');

        new PlainPassword($password);
    }

    /** @return iterable<string, array{string}> */
    public static function shortPasswords(): iterable
    {
        yield '11 caractères' => [str_repeat('a', 11)];
        yield '11 caractères accentués (22 octets)' => [str_repeat('é', 11)];
    }

    public function testRejectsHugePassword(): void
    {
        $this->expectException(InvalidCredentials::class);
        $this->expectExceptionMessage('Le mot de passe ne peut pas dépasser 4096 caractères.');

        new PlainPassword(str_repeat('a', 4097));
    }

    public function testPasswordIsHiddenFromDumps(): void
    {
        self::assertSame(['value' => '***'], (new PlainPassword(str_repeat('s', 12)))->__debugInfo());
    }

    public function testEmailAlreadyUsedIsANeutralConflict(): void
    {
        $error = EmailAlreadyUsed::create();

        self::assertInstanceOf(Conflict::class, $error);
        self::assertSame('Impossible de créer ce compte avec cette adresse e-mail.', $error->getMessage());
    }
}
