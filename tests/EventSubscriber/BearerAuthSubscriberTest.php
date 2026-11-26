<?php

namespace App\Tests\EventSubscriber;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BearerAuthSubscriberTest extends WebTestCase
{
    public function testProtectedEndpointRejectsMissingAndInvalidTokenAndAllowsValidToken(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        static::getContainer()->get('cache.app')->clear();

        $mockHttpClient = new MockHttpClient([
            // Respuesta /auth/me para token inválido
            new MockResponse(json_encode(['message' => 'Unauthenticated']), ['http_code' => 401]),
            // Respuesta /auth/me para token válido
            new MockResponse(json_encode(['data' => ['id' => 1, 'role' => 'admin']]), [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/json'],
            ]),
            // Respuesta /reports/planning tras autenticar
            new MockResponse(json_encode(['data' => ['schedules' => []]]), [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/json'],
            ]),
            // Respuesta /health de Spring para ruta pública /api/health
            new MockResponse(json_encode(['status' => 'UP']), [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/json'],
            ]),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        // 1. Sin token -> 401
        $client->request('GET', '/api/reports/planning', [], [], ['HTTP_X_TEST_ENFORCE_AUTH' => '1']);
        $this->assertResponseStatusCodeSame(401);

        // 2. Token inválido (/auth/me devuelve 401) -> 401
        $client->request('GET', '/api/reports/planning', [], [], [
            'HTTP_X_TEST_ENFORCE_AUTH' => '1',
            'HTTP_AUTHORIZATION' => 'Bearer invalid_tok_123',
        ]);
        $this->assertResponseStatusCodeSame(401);

        // 3. Token válido (/auth/me devuelve 200 con role) -> 200
        $client->request('GET', '/api/reports/planning', [], [], [
            'HTTP_X_TEST_ENFORCE_AUTH' => '1',
            'HTTP_AUTHORIZATION' => 'Bearer valid_tok_456',
        ]);
        $this->assertResponseIsSuccessful();

        // 4. Ruta pública /api/health no requiere Bearer (no devuelve 401)
        $client->request('GET', '/api/health', [], [], ['HTTP_X_TEST_ENFORCE_AUTH' => '1']);
        $this->assertNotSame(401, $client->getResponse()->getStatusCode());
    }
}
