<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FlightScheduleControllerTest extends WebTestCase
{
    public function testWeeklySchedulesAndConflictEndpointsForwardToSpring(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'week' => 43,
                'year' => 2026,
                'schedules' => [
                    ['id' => 10, 'code' => 'FS-2026-0001', 'status' => 'scheduled'],
                ],
                'grouped' => [],
                'planning' => ['status' => 'draft'],
                'latest_request' => null,
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'hasConflict' => true,
                'has_conflicts' => true,
                'code' => 'PILOT_CONFLICT',
                'message' => 'El piloto ya está asignado a OTRA pista en esta fecha.',
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
        ]);

        static::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $client->request('GET', '/api/flight-schedules/weekly/2026/43?pista_id=1');
        $this->assertResponseIsSuccessful();
        $weeklyData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('FS-2026-0001', $weeklyData['schedules'][0]['code']);

        $client->request('POST', '/api/flight-schedules/conflicts', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'scheduled_date' => '2026-10-22',
            'piloto_id' => 1,
            'aeronave_id' => 1,
            'pista_id' => 2,
        ]));
        $this->assertResponseIsSuccessful();
        $conflictData = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($conflictData['has_conflicts']);
        $this->assertSame('PILOT_CONFLICT', $conflictData['code']);
    }
}
