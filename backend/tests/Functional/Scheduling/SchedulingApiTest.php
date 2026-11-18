<?php

declare(strict_types=1);

namespace App\Tests\Functional\Scheduling;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

/**
 * Tests fonctionnels de l'API : de la requête HTTP jusqu'à PostgreSQL et retour.
 */
final class SchedulingApiTest extends WebTestCase
{
    use ClockSensitiveTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        self::mockTime('2026-01-05 07:00');
        $this->signUp('owner@example.com');
    }

    public function testOnlyTheOwnerManagesTheSchedule(): void
    {
        $providerId = $this->registerProvider();
        $this->post("/api/providers/{$providerId}/appointments", ['startsAt' => '2026-01-05T09:00:00', 'customerName' => 'Jane', 'customerEmail' => 'jane@example.com']);
        $appointmentId = self::text($this->json(), 'id');

        $this->post('/api/auth/logout');
        self::assertResponseStatusCodeSame(204);
        $this->get("/api/providers/{$providerId}/appointments?from=2026-01-05&to=2026-01-05");
        self::assertResponseStatusCodeSame(401);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        $this->post("/api/appointments/{$appointmentId}/cancel", ['reason' => 'x']);
        self::assertResponseStatusCodeSame(401);
        self::assertSame(['09:30'], $this->availableStarts($providerId), 'la réservation reste publique');

        $this->signUp('intrus@example.com');
        $this->get("/api/providers/{$providerId}/appointments?from=2026-01-05&to=2026-01-05");
        self::assertResponseStatusCodeSame(403);
        $this->get("/api/appointments/{$appointmentId}");
        self::assertResponseStatusCodeSame(403);
        $this->post("/api/appointments/{$appointmentId}/confirm");
        self::assertResponseStatusCodeSame(403);
        self::assertSame([], $this->get('/api/providers'));
    }

    public function testListsTheAccountsProviders(): void
    {
        $providerId = $this->registerProvider();

        self::assertSame([['id' => $providerId, 'name' => 'Dr Martin']], $this->get('/api/providers'));
    }

    public function testFullBookingJourney(): void
    {
        $providerId = $this->registerProvider();

        self::assertSame(['09:00', '09:30'], $this->availableStarts($providerId));
        self::assertResponseIsSuccessful();

        $this->post("/api/providers/{$providerId}/appointments", [
            'startsAt' => '2026-01-05T09:30:00',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
        ]);
        self::assertResponseStatusCodeSame(201);
        self::assertEmailCount(2);
        self::assertEmailAddressContains(self::getMailerMessage(0) ?? self::fail('e-mail client absent'), 'To', 'jane@example.com');
        self::assertEmailAddressContains(self::getMailerMessage(1) ?? self::fail('e-mail prestataire absent'), 'To', 'owner@example.com');
        $appointmentId = self::text($this->json(), 'id');
        $location = "/api/appointments/{$appointmentId}";
        self::assertResponseHeaderSame('Location', $location);

        $appointment = $this->get($location);
        self::assertSame('pending', self::text($appointment, 'status'));
        self::assertSame('Jane Doe', self::text($appointment, 'customerName'));
        self::assertSame(['09:00'], $this->availableStarts($providerId));

        $this->post("{$location}/confirm");
        self::assertResponseStatusCodeSame(204);
        self::assertEmailCount(1);
        self::assertEmailSubjectContains(self::getMailerMessage() ?? self::fail('e-mail absent'), 'Rendez-vous confirmé');
        self::assertSame('confirmed', self::text($this->get($location), 'status'));

        $this->post("{$location}/cancel", ['reason' => 'Empêchement']);
        self::assertResponseStatusCodeSame(204);
        self::assertEmailCount(1);
        self::assertEmailTextBodyContains(self::getMailerMessage() ?? self::fail('e-mail absent'), 'Motif : Empêchement');

        $agenda = $this->get("/api/providers/{$providerId}/appointments?from=2026-01-05&to=2026-01-05");
        self::assertCount(1, $agenda);
        self::assertSame('cancelled', self::text(self::object($agenda[0] ?? null), 'status'));
    }

    public function testDoubleBookingIsAConflictWithReasons(): void
    {
        $providerId = $this->registerProvider();
        $booking = ['startsAt' => '2026-01-05T09:00:00', 'customerName' => 'Jane', 'customerEmail' => 'jane@example.com'];
        $this->post("/api/providers/{$providerId}/appointments", $booking);
        self::assertResponseStatusCodeSame(201);

        $this->post("/api/providers/{$providerId}/appointments", $booking);

        self::assertResponseStatusCodeSame(409);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        $problem = $this->json();
        self::assertSame(409, $problem['status'] ?? null);
        self::assertSame(['déjà réservé'], $problem['reasons'] ?? null);
    }

    public function testForbiddenTransitionIsAConflict(): void
    {
        $providerId = $this->registerProvider();
        $this->post("/api/providers/{$providerId}/appointments", ['startsAt' => '2026-01-05T09:00:00', 'customerName' => 'Jane', 'customerEmail' => 'jane@example.com']);
        $location = '/api/appointments/'.self::text($this->json(), 'id');
        $this->post("{$location}/cancel", ['reason' => 'x']);

        $this->post("{$location}/confirm");

        self::assertResponseStatusCodeSame(409);
        self::assertSame([], $this->json()['reasons'] ?? null);
    }

    public function testUnknownResourcesAre404(): void
    {
        $this->get('/api/appointments/inconnu');
        self::assertResponseStatusCodeSame(404);
        self::assertSame('Rendez-vous « inconnu » introuvable.', self::text($this->json(), 'detail'));

        $this->get('/api/providers/inconnu/slots?date=2026-01-05');
        self::assertResponseStatusCodeSame(404);
    }

    public function testInvalidPayloadListsViolations(): void
    {
        $providerId = $this->registerProvider();

        $this->post("/api/providers/{$providerId}/appointments", ['startsAt' => '2026-01-05T09:00:00', 'customerName' => '', 'customerEmail' => 'pas-un-mail']);

        self::assertResponseStatusCodeSame(422);
        $fields = array_map(static fn (mixed $v): string => self::text(self::object($v), 'field'), self::list($this->json()['violations'] ?? null));
        self::assertContains('customerName', $fields);
        self::assertContains('customerEmail', $fields);
    }

    public function testBusinessRuleViolationIs422(): void
    {
        $this->post('/api/providers', [
            'name' => 'Dr Martin',
            'openingHours' => [['weekday' => 1, 'opensAt' => '12:00', 'closesAt' => '09:00']],
            'slotDurationMinutes' => 30,
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('fermeture', self::text($this->json(), 'detail'));
    }

    public function testInvalidQueryStringIs400(): void
    {
        $providerId = $this->registerProvider();

        $this->get("/api/providers/{$providerId}/slots?date=2026-13-45");
        self::assertResponseStatusCodeSame(400);

        $this->get("/api/providers/{$providerId}/appointments?from=2026-01-06&to=2026-01-05");
        self::assertResponseStatusCodeSame(400);
    }

    public function testMalformedJsonIs400(): void
    {
        $this->client->request('POST', '/api/providers', server: ['CONTENT_TYPE' => 'application/json'], content: '{pas du json');

        self::assertResponseStatusCodeSame(400);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
    }

    public function testOutsideApiErrorsAreLeftToSymfony(): void
    {
        $this->client->request('GET', '/inexistant');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('Content-Type', 'text/html; charset=UTF-8');
    }

    /** Crée un compte : l'API connecte aussitôt le client (cookie de session). */
    private function signUp(string $email): void
    {
        $this->post('/api/auth/register', ['email' => $email, 'password' => 'un mot de passe solide']);
        self::assertResponseStatusCodeSame(201);
    }

    private function registerProvider(): string
    {
        $this->post('/api/providers', [
            'name' => 'Dr Martin',
            'openingHours' => [['weekday' => 1, 'opensAt' => '09:00', 'closesAt' => '10:00']],
            'slotDurationMinutes' => 30,
            'minimumNoticeMinutes' => 60,
        ]);
        self::assertResponseStatusCodeSame(201);

        return self::text($this->json(), 'id');
    }

    /**
     * @return list<string> heures de début (HH:MM) des créneaux libres du lundi 5 janvier
     */
    private function availableStarts(string $providerId): array
    {
        return array_map(
            static fn (mixed $slot): string => substr(self::text(self::object($slot), 'startsAt'), 11, 5),
            self::list($this->get("/api/providers/{$providerId}/slots?date=2026-01-05")),
        );
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
    private function get(string $uri): array
    {
        $this->client->request('GET', $uri);

        return $this->json();
    }

    /**
     * @return array<mixed>
     */
    private function json(): array
    {
        $content = $this->client->getResponse()->getContent();
        self::assertIsString($content);

        return self::object(json_decode($content, true, 512, \JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<mixed>
     */
    private static function object(mixed $value): array
    {
        self::assertIsArray($value);

        return $value;
    }

    /**
     * @return list<mixed>
     */
    private static function list(mixed $value): array
    {
        self::assertIsList($value);

        return $value;
    }

    /**
     * @param array<mixed> $data
     */
    private static function text(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        self::assertIsString($value);

        return $value;
    }
}
