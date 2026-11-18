<?php

declare(strict_types=1);

namespace App\Tests\Functional\Identity;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

final class PasswordResetApiTest extends WebTestCase
{
    private const string OLD_PASSWORD = 'ancien mot de passe';
    private const string NEW_PASSWORD = 'nouveau mot de passe';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        foreach (['cache.rate_limiter'] as $pool) {
            $storage = self::getContainer()->get($pool);
            self::assertInstanceOf(CacheItemPoolInterface::class, $storage);
            $storage->clear();
        }
        $this->post('/api/auth/register', ['email' => 'jane@example.com', 'password' => self::OLD_PASSWORD]);
        $this->post('/api/auth/logout');
    }

    public function testFullResetJourney(): void
    {
        $this->post('/api/auth/password-reset', ['email' => 'Jane@Example.com']);
        self::assertResponseStatusCodeSame(202);
        self::assertEmailCount(1);
        $token = $this->tokenFromLastEmail();

        $this->post('/api/auth/password-reset/confirm', ['token' => $token, 'password' => self::NEW_PASSWORD]);
        self::assertResponseStatusCodeSame(204);

        $this->post('/api/auth/login', ['email' => 'jane@example.com', 'password' => self::OLD_PASSWORD]);
        self::assertResponseStatusCodeSame(401);
        $this->post('/api/auth/login', ['email' => 'jane@example.com', 'password' => self::NEW_PASSWORD]);
        self::assertResponseIsSuccessful();

        $this->post('/api/auth/password-reset/confirm', ['token' => $token, 'password' => 'encore un autre mot']);
        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
    }

    public function testUnknownEmailGetsTheSameAnswerButNoEmail(): void
    {
        $this->post('/api/auth/password-reset', ['email' => 'inconnu@example.com']);

        self::assertResponseStatusCodeSame(202);
        self::assertEmailCount(0);
    }

    public function testRequestsAreThrottled(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $this->post('/api/auth/password-reset', ['email' => 'inconnu@example.com']);
            self::assertResponseStatusCodeSame(202);
        }

        $this->post('/api/auth/password-reset', ['email' => 'inconnu@example.com']);

        self::assertResponseStatusCodeSame(429);
    }

    public function testInvalidTokenAndWeakPassword(): void
    {
        $this->post('/api/auth/password-reset/confirm', ['token' => 'inconnu', 'password' => self::NEW_PASSWORD]);
        self::assertResponseStatusCodeSame(422);

        $this->post('/api/auth/password-reset/confirm', ['token' => 'inconnu', 'password' => 'court']);
        self::assertResponseStatusCodeSame(422);
    }

    private function tokenFromLastEmail(): string
    {
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', 'jane@example.com');
        preg_match('#/mot-de-passe/nouveau\?token=([0-9a-f]{64})#', (string) $email->getTextBody(), $matches);
        $token = $matches[1] ?? null;
        self::assertIsString($token, 'lien de réinitialisation absent de l\'e-mail');

        return $token;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function post(string $uri, array $body = []): void
    {
        $this->client->request('POST', $uri, server: ['CONTENT_TYPE' => 'application/json'], content: json_encode($body, \JSON_THROW_ON_ERROR));
    }
}
