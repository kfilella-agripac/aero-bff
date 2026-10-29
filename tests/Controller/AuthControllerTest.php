<?php

namespace App\Tests\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

class AuthControllerTest extends WebTestCase
{
    private function replaceSpringClientWithMock(callable|array $responses): void
    {
        $mockHttpClient = new MockHttpClient($responses);
        $container = static::getContainer();
        $springClient = new SpringBackendClient(
            $mockHttpClient,
            $container->get(RequestStack::class),
            $container->get(Security::class),
            'http://backend-spring:8080/api/internal',
            'test-secret'
        );
        $container->set(SpringBackendClient::class, $springClient);
    }

    public function testLoginRouteForwardsAndReturnsUnauthorizedOnInvalidCredentials(): void
    {
        $client = static::createClient();
        $this->replaceSpringClientWithMock([
            new MockResponse(json_encode(['message' => 'Credenciales inválidas']), [
                'http_code' => 401,
                'response_headers' => ['content-type' => 'application/json'],
            ]),
        ]);

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'invalid@user.com',
            'password' => 'wrong',
        ]));

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testCatalogRoutesForwardToSpringBackend(): void
    {
        $client = static::createClient();
        $this->replaceSpringClientWithMock([
            new MockResponse(json_encode([['id' => 1, 'name' => 'Pista Taura']]), [
                'http_code' => 200,
                'response_headers' => ['content-type' => 'application/json'],
            ]),
        ]);

        $client->request('GET', '/api/pistas');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Pista Taura', $data[0]['name']);
    }
}
