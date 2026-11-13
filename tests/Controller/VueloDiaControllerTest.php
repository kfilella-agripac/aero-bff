<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VueloDiaControllerTest extends WebTestCase
{
    public function testVueloDiaEndpointsAndSignatureForwardToSpring(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $pngStub = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 1, 'num_vuelo' => 'VD-584467'],
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'success' => true,
                'data' => ['flight_schedule_id' => 17, 'num_vuelo' => 'REP-000017'],
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'message' => 'Vuelo del día creado exitosamente',
                'data' => ['id' => 3, 'num_vuelo' => 'VD-584468'],
            ]), ['http_code' => 201, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'id' => 3,
                'num_vuelo' => 'VD-584468',
                'firma_mecanico_url' => '/api/vuelos-dia/3/firma/mecanico',
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'message' => 'Vuelo del día actualizado exitosamente',
                'data' => ['id' => 3, 'num_vuelo' => 'VD-584468'],
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse($pngStub, [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'image/png'],
            ]),
            new MockResponse(json_encode([
                'message' => 'Vuelo del día eliminado exitosamente',
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
        ]);

        static::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $client->request('GET', '/api/vuelos-dia');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/vuelos-dia/prefill/17');
        $this->assertResponseIsSuccessful();

        $client->request('POST', '/api/vuelos-dia', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['flight_schedule_id' => 17, 'num_vuelo' => 'VD-584468', 'mecanico_id' => 1]));
        $this->assertResponseStatusCodeSame(201);

        $client->request('GET', '/api/vuelos-dia/3');
        $this->assertResponseIsSuccessful();
        $item = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('/api/vuelos-dia/3/firma/mecanico', $item['firma_mecanico_url']);

        $client->request('PUT', '/api/vuelos-dia/3', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['arranques_dia' => 3]));
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/vuelos-dia/3/firma/mecanico');
        $this->assertResponseIsSuccessful();
        $this->assertSame('image/png', $client->getResponse()->headers->get('Content-Type'));

        $client->request('DELETE', '/api/vuelos-dia/3');
        $this->assertResponseIsSuccessful();
    }
}
