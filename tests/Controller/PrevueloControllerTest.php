<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PrevueloControllerTest extends WebTestCase
{
    public function testPrevueloEndpointsForwardToSpring(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'data' => [['id' => 1, 'aircraft_type' => 'cessna', 'status' => 'completed', 'is_airworthy' => true]],
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'success' => true,
                'data' => ['id' => 2, 'aircraft_type' => 'thrush', 'status' => 'pending'],
            ]), ['http_code' => 201, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'data' => [['id' => 10, 'code' => '584451']],
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'data' => [['id' => 10, 'code' => '584451']],
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'next_tachometer' => 1250.5,
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'data' => ['id' => 2, 'aircraft_type' => 'thrush', 'status' => 'pending'],
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'data' => ['id' => 2, 'aircraft_type' => 'thrush', 'status' => 'completed', 'is_airworthy' => true],
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'success' => true,
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'success' => true,
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
        ]);

        static::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $client->request('GET', '/api/prevuelos');
        $this->assertResponseIsSuccessful();

        $client->request('POST', '/api/prevuelos', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['aircraft_type' => 'thrush', 'flight_schedule_id' => 10]));
        $this->assertResponseStatusCodeSame(201);

        $client->request('GET', '/api/prevuelos/available-schedules');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/prevuelos/all-scheduled-flights');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/prevuelos/next-tachometer?aeronave_id=1');
        $this->assertResponseIsSuccessful();
        $nextTac = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(1250.5, $nextTac['next_tachometer']);

        $client->request('GET', '/api/prevuelos/thrush/2');
        $this->assertResponseIsSuccessful();

        $client->request('PUT', '/api/prevuelos/thrush/2', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['is_airworthy' => true, 'status' => 'completed']));
        $this->assertResponseIsSuccessful();

        $client->request('PATCH', '/api/prevuelos/thrush/2/date', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['scheduled_date' => '2026-11-12']));
        $this->assertResponseIsSuccessful();

        $client->request('DELETE', '/api/prevuelos/thrush/2');
        $this->assertResponseIsSuccessful();
    }
}
