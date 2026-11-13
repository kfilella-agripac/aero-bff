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

    public function testSignedFileRoutesStreamToSpringWithoutSession(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $calls = [];
        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$calls): MockResponse {
            $body = $options['body'] ?? '';
            if (is_resource($body)) {
                $body = stream_get_contents($body); // el BFF reenvía la subida como stream
            } elseif (is_callable($body)) {
                $content = '';
                while ('' !== ($part = $body(8192))) {
                    $content .= $part;
                }
                $body = $content;
            }
            $calls[] = ['method' => $method, 'path' => parse_url($url, PHP_URL_PATH),
                'headers' => implode("\n", $options['headers'] ?? []), 'body' => $body];

            if (str_ends_with(parse_url($url, PHP_URL_PATH), '/auth/me')) {
                return new MockResponse(json_encode(['id' => 3, 'role' => 'fitosanitario']), [
                    'http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json'],
                ]);
            }
            if ($method === 'PUT') {
                return new MockResponse('{"message":"Archivo recibido."}', [
                    'http_code' => 200, 'response_headers' => ['Content-Type' => 'application/json', 'ETag' => '"abc"'],
                ]);
            }

            return new MockResponse('%PDF-firma', ['http_code' => 200, 'response_headers' => [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="orden.pdf"',
            ]]);
        });
        static::getContainer()->set(HttpClientInterface::class, $mock);

        $client->request('PUT', '/api/files/upload/eyJvcCI6InB1dCJ9.c2ln', [], [], [
            'CONTENT_TYPE' => 'application/pdf', 'HTTP_CONTENT_MD5' => 'bWQ1',
        ], '%PDF-1.4');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('ETag', '"abc"');

        // Un fitosanitario puede abrir su firma con la URL firmada.
        $client->request('GET', '/api/files/eyJvcCI6ImdldCJ9.c2ln', [], [], ['HTTP_AUTHORIZATION' => 'Bearer tok-fito']);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Disposition', 'inline; filename="orden.pdf"');
        $this->assertSame('%PDF-firma', $client->getInternalResponse()->getContent());

        $client->request('GET', '/api/files/sin-firma');
        $this->assertResponseStatusCodeSame(404);

        $forwarded = array_values(array_filter($calls, fn (array $c) => !str_ends_with($c['path'], '/auth/me')));
        $this->assertSame('PUT', $forwarded[0]['method']);
        $this->assertStringEndsWith('/api/internal/files/upload/eyJvcCI6InB1dCJ9.c2ln', $forwarded[0]['path']);
        $this->assertSame('%PDF-1.4', $forwarded[0]['body']);
        $this->assertStringContainsStringIgnoringCase('content-type: application/pdf', $forwarded[0]['headers']);
        $this->assertStringContainsStringIgnoringCase('content-md5: bWQ1', $forwarded[0]['headers']);
        $this->assertStringContainsStringIgnoringCase('x-internal-secret:', $forwarded[0]['headers']);
        $this->assertStringEndsWith('/api/internal/files/eyJvcCI6ImdldCJ9.c2ln', $forwarded[1]['path']);
    }

    public function testMultipartNovedadAndBlockAreRebuiltForSpring(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $calls = [];
        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$calls): MockResponse {
            $body = '';
            if (isset($options['body']) && is_callable($options['body'])) {
                while ('' !== ($part = ($options['body'])(8192))) {
                    $body .= $part;
                }
            } else {
                $body = (string) ($options['body'] ?? '');
            }
            $calls[] = ['path' => parse_url($url, PHP_URL_PATH), 'headers' => implode("\n", $options['headers'] ?? []), 'body' => $body];

            return new MockResponse('{"id":1}', ['http_code' => 201, 'response_headers' => ['Content-Type' => 'application/json']]);
        });
        static::getContainer()->set(HttpClientInterface::class, $mock);

        $tmp = tempnam(sys_get_temp_dir(), 'nov');
        file_put_contents($tmp, '%PDF-adjunto');
        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile($tmp, 'informe técnico.pdf', 'application/pdf', null, true);

        $client->request('POST', '/api/novedades', ['aeronave_id' => '2', 'novedad_tipo_id' => '5', 'fecha' => '2026-11-12'],
            ['documents' => [$file]], ['HTTP_AUTHORIZATION' => 'Bearer tok-jefe', 'CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertResponseStatusCodeSame(201);

        $client->request('POST', '/api/flight-schedules/9/block', ['novedad_tipo_id' => '3', 'reason' => 'Lluvia intensa en pista'],
            ['documents' => [$file]], ['HTTP_AUTHORIZATION' => 'Bearer tok-jefe', 'CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertResponseStatusCodeSame(201);

        $calls = array_values(array_filter($calls, fn (array $c) => !str_ends_with($c['path'], '/auth/me')));
        foreach ([[0, '/novedades', 'aeronave_id'], [1, '/flight-schedules/9/block', 'reason']] as [$i, $path, $field]) {
            $call = $calls[$i];
            $this->assertStringEndsWith('/api/internal' . $path, $call['path']);
            $this->assertMatchesRegularExpression('/content-type: multipart\/form-data; boundary=saero-\w+/i', $call['headers']);
            $this->assertStringContainsStringIgnoringCase('authorization: Bearer tok-jefe', $call['headers']);
            $this->assertStringContainsString('name="' . $field . '"', $call['body']);
            $this->assertStringContainsString('name="documents[]"; filename="informe técnico.pdf"', $call['body']);
            $this->assertStringContainsString("Content-Type: application/pdf\r\n\r\n%PDF-adjunto\r\n", $call['body']);
        }
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
