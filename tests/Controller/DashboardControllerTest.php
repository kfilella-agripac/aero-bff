<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DashboardControllerTest extends WebTestCase
{
    public function testDashboardEndpointsForwardToSpring(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'total_pilots' => 5,
                'total_aircraft' => 4,
                'flights_this_week' => 15,
                'critical_alerts' => 0,
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'medical_certificates' => [],
                'aircraft_registrations' => [],
                'total_expired' => 0,
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'medical_certificates' => [],
                'aircraft_registrations' => [],
                'total_expiring' => 0,
                'days' => 30,
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
            new MockResponse(json_encode([
                'week' => 46,
                'year' => 2026,
                'total_schedules' => 15,
            ]), ['http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json']]),
        ]);

        static::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $client->request('GET', '/api/dashboard/overview');
        $this->assertResponseIsSuccessful();
        $overview = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(5, $overview['total_pilots']);

        $client->request('GET', '/api/dashboard/expired-documents');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/dashboard/expiring-soon?days=30');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/dashboard/weekly-summary/2026/46');
        $this->assertResponseIsSuccessful();
    }
}
