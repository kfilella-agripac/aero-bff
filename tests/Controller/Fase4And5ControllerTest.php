<?php

namespace App\Tests\Controller;

use App\EventSubscriber\FitosanitarioAccessSubscriber;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fases 4.4 y 5.4: el BFF reenvía a las rutas internas de Spring y aplica la lista
 * permitida del rol fitosanitario.
 */
class Fase4And5ControllerTest extends WebTestCase
{
    /** @var list<array{method: string, url: string, headers: array}> */
    private array $calls = [];

    /** @param array<string, string> $roles token => rol que devuelve /auth/me */
    private function client(array $roles = []): KernelBrowser
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->calls = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use ($roles): MockResponse {
            $headers = [];
            foreach ($options['headers'] ?? [] as $line) {
                [$name, $value] = explode(':', $line, 2) + [1 => ''];
                $headers[strtolower(trim($name))] = trim($value);
            }
            $this->calls[] = ['method' => $method, 'url' => $url, 'headers' => $headers];

            if (str_ends_with(parse_url($url, PHP_URL_PATH), '/auth/me')) {
                $token = preg_replace('/^Bearer\s+/i', '', $headers['authorization'] ?? '');
                if (!isset($roles[$token])) {
                    return new MockResponse('{"message":"No autenticado"}', ['http_code' => 401]);
                }

                return new MockResponse(json_encode(['id' => 1, 'role' => $roles[$token]]), [
                    'http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json'],
                ]);
            }

            return new MockResponse(json_encode(['ok' => true, 'path' => parse_url($url, PHP_URL_PATH)]), [
                'http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json'],
            ]);
        });
        static::getContainer()->set(HttpClientInterface::class, $mock);

        return $client;
    }

    /** @return list<string> rutas de Spring llamadas, sin /auth/me */
    private function forwardedPaths(): array
    {
        $paths = [];
        foreach ($this->calls as $call) {
            $path = parse_url($call['url'], PHP_URL_PATH);
            if (!str_ends_with($path, '/auth/me')) {
                $paths[] = $call['method'] . ' ' . preg_replace('#^.*/api/internal#', '', $path);
            }
        }

        return $paths;
    }

    public function testFase4RoutesForwardToSpring(): void
    {
        $client = $this->client();
        $json = ['CONTENT_TYPE' => 'application/json'];

        $client->request('GET', '/api/fumigation-orders?planning_status=unplanned');
        $client->request('POST', '/api/fumigation-orders', [], [], $json, '{"hectares":10}');
        $client->request('PUT', '/api/fumigation-orders/5', [], [], $json, '{"observations":"x"}');
        $client->request('DELETE', '/api/fumigation-orders/5');
        $client->request('GET', '/api/fitosanitarios');
        $client->request('PUT', '/api/fitosanitarios/8', [], [], $json, '{"name":"Fito"}');
        $client->request('GET', '/api/novedades?entity_type=aeronave');
        $client->request('DELETE', '/api/novedades/3');
        $client->request('GET', '/api/novedad-tipos');
        $client->request('POST', '/api/novedad-tipos', [], [], $json, '{"nombre":"Granizo"}');
        $client->request('POST', '/api/documents/presigned-url', [], [], $json, '{"filename":"a.pdf"}');
        $client->request('POST', '/api/documents/complete-upload', [], [], $json, '{"upload_token":"t"}');
        $client->request('GET', '/api/documents?documentable_type=aeronave&documentable_id=1');
        $client->request('GET', '/api/documents/9/file');

        $this->assertResponseIsSuccessful();
        $this->assertSame([
            'GET /fumigation-orders',
            'POST /fumigation-orders',
            'PUT /fumigation-orders/5',
            'DELETE /fumigation-orders/5',
            'GET /fitosanitarios',
            'PUT /fitosanitarios/8',
            'GET /novedades',
            'DELETE /novedades/3',
            'GET /novedad-tipos',
            'POST /novedad-tipos',
            'POST /documents/presigned-url',
            'POST /documents/complete-upload',
            'GET /documents',
            'GET /documents/9/file',
        ], $this->forwardedPaths());
        $this->assertStringContainsString('planning_status=unplanned', $this->calls[0]['url']);
    }

    public function testFase5RoutesForwardToSpringWithToken(): void
    {
        $client = $this->client();
        $headers = ['HTTP_AUTHORIZATION' => 'Bearer tok-piloto', 'CONTENT_TYPE' => 'application/json'];

        $client->request('GET', '/api/fuel-controls/eligible-flights?report_date=2026-11-05', [], [], $headers);
        $client->request('GET', '/api/fuel-controls?page=2', [], [], $headers);
        $client->request('POST', '/api/fuel-controls', [], [], $headers, '{"entries":[]}');
        $client->request('PUT', '/api/fuel-controls/4', [], [], $headers, '{"expected_revision":1}');
        $client->request('GET', '/api/fuel-controls/4/signature/pilot', [], [], $headers);
        $client->request('POST', '/api/fuel-controls/4/restore', [], [], $headers);
        $client->request('GET', '/api/reports/operations/fuel-anomalies?tolerance_percent=15', [], [], $headers);

        $this->assertSame([
            'GET /fuel-controls/eligible-flights',
            'GET /fuel-controls',
            'POST /fuel-controls',
            'PUT /fuel-controls/4',
            'GET /fuel-controls/4/signature/pilot',
            'POST /fuel-controls/4/restore',
            'GET /reports/operations/fuel-anomalies',
        ], $this->forwardedPaths());
        foreach ($this->calls as $call) {
            if (!str_ends_with(parse_url($call['url'], PHP_URL_PATH), '/auth/me')) {
                $this->assertSame('Bearer tok-piloto', $call['headers']['authorization'] ?? null);
            }
        }

        $client->request('GET', '/api/fuel-controls/4/signature/other', [], [], $headers);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testFitosanitarioIsLimitedToTheAllowList(): void
    {
        $client = $this->client(['tok-fito' => 'fitosanitario', 'tok-admin' => 'admin']);
        $fito = ['HTTP_AUTHORIZATION' => 'Bearer tok-fito', 'CONTENT_TYPE' => 'application/json'];

        $client->request('GET', '/api/fumigation-orders', [], [], $fito);
        $this->assertResponseIsSuccessful();
        $client->request('PUT', '/api/fumigation-orders/3', [], [], $fito, '{}');
        $this->assertResponseIsSuccessful();
        $client->request('GET', '/api/fincas/2/sectors', [], [], $fito);
        $this->assertResponseIsSuccessful();

        foreach ([
            ['GET', '/api/flight-schedules'],
            ['GET', '/api/novedades'],
            ['GET', '/api/fuel-controls'],
            ['POST', '/api/flight-schedules/1/block'],
            ['DELETE', '/api/products/1'],
        ] as [$method, $uri]) {
            $client->request($method, $uri, [], [], $fito, '{}');
            $this->assertResponseStatusCodeSame(403, "$method $uri");
            $this->assertSame('El usuario fitosanitario no tiene acceso a esta acción.',
                json_decode($client->getResponse()->getContent(), true)['message']);
        }

        // Otros roles y peticiones sin token no se ven afectados.
        $client->request('GET', '/api/flight-schedules', [], [], ['HTTP_AUTHORIZATION' => 'Bearer tok-admin']);
        $this->assertResponseIsSuccessful();
        $client->request('GET', '/api/novedades');
        $this->assertResponseIsSuccessful();

        // Las rutas bloqueadas no llegan a Spring.
        $this->assertSame(['GET /fumigation-orders', 'PUT /fumigation-orders/3', 'GET /fincas/2/sectors',
            'GET /flight-schedules', 'GET /novedades'], array_map(
                fn (string $p) => preg_replace('#^(\w+) /catalogs#', '$1 ', $p),
                $this->forwardedPaths(),
            ));
    }

    public function testAllowListMatchesLaravelRoutes(): void
    {
        $this->assertTrue(FitosanitarioAccessSubscriber::isAllowed('GET', 'me'));
        $this->assertTrue(FitosanitarioAccessSubscriber::isAllowed('GET', 'fumigation-orders/12/pdf'));
        $this->assertTrue(FitosanitarioAccessSubscriber::isAllowed('POST', 'products'));
        $this->assertTrue(FitosanitarioAccessSubscriber::isAllowed('PATCH', 'products/4'));
        $this->assertFalse(FitosanitarioAccessSubscriber::isAllowed('DELETE', 'products/4'));
        $this->assertFalse(FitosanitarioAccessSubscriber::isAllowed('POST', 'fumigation-orders/4'));
        $this->assertFalse(FitosanitarioAccessSubscriber::isAllowed('GET', 'fumigation-orders/abc'));
    }
}
