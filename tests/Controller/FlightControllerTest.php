<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FlightControllerTest extends WebTestCase
{
    public function testFlightUpdateAndDeleteForwardToSpring(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'success' => true,
                'data' => ['id' => 5, 'tacometro_salida' => 1250.5, 'tacometro_llegada' => 1252.2],
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'success' => true,
                'message' => 'Vuelo eliminado correctamente',
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
        ]);

        static::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $client->request('PUT', '/api/flights/5', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['tacometro_llegada' => 1252.2]));
        $this->assertResponseIsSuccessful();
        $updated = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(1252.2, $updated['data']['tacometro_llegada']);

        $client->request('DELETE', '/api/flights/5');
        $this->assertResponseIsSuccessful();
        $deleted = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($deleted['success']);
    }
}
