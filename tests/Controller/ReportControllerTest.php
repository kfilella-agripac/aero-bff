<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ReportControllerTest extends WebTestCase
{
    public function testReportsExcelPdfAndWeeklyDispatchForwardToSpring(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode(['data' => ['schedules' => [], 'charts' => []]]), [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/json'],
            ]),
            new MockResponse(json_encode(['data' => ['pistas' => [], 'summary' => []]]), [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/json'],
            ]),
            new MockResponse('PK-FAKE-XLSX', [
                'http_code' => 200,
                'response_headers' => [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="planificacion_semanal_pistas_2026_w46.xlsx"',
                ],
            ]),
            new MockResponse('%PDF-1.4-PLANNING', [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/pdf'],
            ]),
            new MockResponse('%PDF-1.4-FUEL-CONTROL', [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/pdf'],
            ]),
            new MockResponse('%PDF-1.4-FUMIGATION-ORDER', [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/pdf'],
            ]),
            new MockResponse('%PDF-1.4-FUEL-ANOMALIES', [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/pdf'],
            ]),
            new MockResponse(json_encode(['success' => true, 'data' => ['sent' => 2, 'total' => 2]]), [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/json'],
            ]),
        ]);

        static::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $client->request('GET', '/api/reports/planning');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/reports/planning/pistas');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/reports/planning/pistas/excel?week_number=46&year=2026');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $client->request('POST', '/api/reports/planning/pdf', [], [], ['CONTENT_TYPE' => 'application/json'], '{}');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/pdf');

        $client->request('GET', '/api/fuel-controls/1/pdf');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/pdf');

        $client->request('GET', '/api/fumigation-orders/1/pdf');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/pdf');

        $client->request('POST', '/api/reports/operations/fuel-anomalies/pdf', [], [], ['CONTENT_TYPE' => 'application/json'], '{}');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/pdf');

        $client->request('POST', '/api/reports/planning/send-weekly', [], [], ['CONTENT_TYPE' => 'application/json'], '{"week":46,"year":2026}');
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(2, $data['data']['sent']);
    }
}
