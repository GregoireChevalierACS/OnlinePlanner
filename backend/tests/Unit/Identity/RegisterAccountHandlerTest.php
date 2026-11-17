<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity;

use App\Identity\Application\PasswordHasher;
use App\Identity\Application\RegisterAccount;
use App\Identity\Application\RegisterAccountHandler;
use App\Identity\Domain\EmailAlreadyUsed;
use App\Identity\Domain\InvalidCredentials;
use App\Identity\Domain\PlainPassword;
use App\Identity\Infrastructure\Persistence\InMemoryAccountRepository;
use PHPUnit\Framework\TestCase;

final class RegisterAccountHandlerTest extends TestCase
{
    private InMemoryAccountRepository $accounts;
    private RegisterAccountHandler $handler;

    protected function setUp(): void
    {
        $this->accounts = new InMemoryAccountRepository();
        $hasher = new class implements PasswordHasher {
            public function hash(PlainPassword $password): string
            {
                return 'hashed:'.$password->value;
            }
        };
        $this->handler = new RegisterAccountHandler($this->accounts, $hasher);
    }

    public function testStoresTheAccountWithAHashedPassword(): void
    {
        ($this->handler)(new RegisterAccount('acc-1', 'Jane@Example.com', 'correct horse battery'));

        $account = $this->accounts->findByEmail('jane@example.com');
        self::assertNotNull($account);
        self::assertSame('acc-1', $account->id()->value);
        self::assertSame('hashed:correct horse battery', $account->passwordHash());
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
        }
    }
}
