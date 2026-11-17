<?php

declare(strict_types=1);

namespace App\Tests\Functional\Identity;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthApiTest extends WebTestCase
{
    private const string PASSWORD = 'un mot de passe solide';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        // Les compteurs de tentatives de connexion survivent d'un test (et d'une exécution) à l'autre : on repart de zéro.
        $limiterStorage = self::getContainer()->get('cache.rate_limiter');
        self::assertInstanceOf(CacheItemPoolInterface::class, $limiterStorage);
        $limiterStorage->clear();
    }

    public function testRegisterLogsTheUserIn(): void
    {
        $this->post('/api/auth/register', ['email' => 'Jane@Example.com', 'password' => self::PASSWORD]);

        self::assertResponseStatusCodeSame(201);
        $created = $this->json();
        self::assertSame('jane@example.com', $created['email'] ?? null);

        $this->client->request('GET', '/api/auth/me');
        self::assertResponseIsSuccessful();
        self::assertSame($created, $this->json());
    }

    public function testLoginLogoutCycle(): void
    {
        $this->post('/api/auth/register', ['email' => 'jane@example.com', 'password' => self::PASSWORD]);
        $this->post('/api/auth/logout');
        self::assertResponseStatusCodeSame(204);

        $this->client->request('GET', '/api/auth/me');
        self::assertResponseStatusCodeSame(401);

        $this->post('/api/auth/login', ['email' => 'JANE@example.com', 'password' => self::PASSWORD]);
        self::assertResponseIsSuccessful();
        self::assertSame('jane@example.com', $this->json()['email'] ?? null);

        $this->client->request('GET', '/api/auth/me');
        self::assertResponseIsSuccessful();
    }

    public function testWrongPasswordAndUnknownEmailGetTheSameAnswer(): void
    {
        $this->post('/api/auth/register', ['email' => 'jane@example.com', 'password' => self::PASSWORD]);
        $this->post('/api/auth/logout');

        $this->post('/api/auth/login', ['email' => 'jane@example.com', 'password' => 'mauvais mot de passe']);
        self::assertResponseStatusCodeSame(401);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        $wrongPassword = $this->json();

        $this->post('/api/auth/login', ['email' => 'inconnu@example.com', 'password' => self::PASSWORD]);
        self::assertResponseStatusCodeSame(401);
        self::assertSame($wrongPassword, $this->json());
        self::assertSame('Adresse e-mail ou mot de passe incorrect.', $wrongPassword['detail'] ?? null);
    }

    public function testDuplicateEmailIsAConflict(): void
    {
        $this->post('/api/auth/register', ['email' => 'jane@example.com', 'password' => self::PASSWORD]);
        $this->post('/api/auth/logout');

        $this->post('/api/auth/register', ['email' => 'JANE@example.com', 'password' => 'un autre mot de passe']);

        self::assertResponseStatusCodeSame(409);
    }

    public function testWeakPasswordIsRejected(): void
    {
        $this->post('/api/auth/register', ['email' => 'jane@example.com', 'password' => 'court']);

        self::assertResponseStatusCodeSame(422);
        $violations = $this->json()['violations'] ?? null;
        self::assertIsArray($violations);
        $first = $violations[0] ?? null;
        self::assertIsArray($first);
        self::assertSame('password', $first['field'] ?? null);
    }

    public function testLoginIsThrottled(): void
    {
        for ($attempt = 1; $attempt <= 5; ++$attempt) {
            $this->post('/api/auth/login', ['email' => 'cible@example.com', 'password' => 'essai '.$attempt]);
            self::assertResponseStatusCodeSame(401);
        }

        $this->post('/api/auth/login', ['email' => 'cible@example.com', 'password' => 'essai 6']);

        self::assertResponseStatusCodeSame(429);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function post(string $uri, array $body = []): void
    {
        $this->client->request('POST', $uri, server: ['CONTENT_TYPE' => 'application/json'], content: json_encode($body, \JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<mixed>
     */
    private function json(): array
    {
        $content = $this->client->getResponse()->getContent();
        self::assertIsString($content);
        $data = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }
}
