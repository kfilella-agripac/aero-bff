<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FlightScheduleControllerTest extends WebTestCase
{
    public function testNextCodeRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/flight-schedules/next-code');

        $this->assertNotEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testWeeklyRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/flight-schedules/weekly/2026/42');

        $this->assertNotEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testWeekStatusRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/flight-schedules/week/42/year/2026/status');

        $this->assertNotEquals(404, $client->getResponse()->getStatusCode());
    }
}
