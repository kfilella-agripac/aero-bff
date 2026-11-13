<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Archivos del servidor privado (documentos, adjuntos y firmas) con URL firmada por Spring.
 * Sustituye a las URLs prefirmadas de S3: el navegador sube con PUT y descarga con GET
 * sin token de sesión; la autorización es el token firmado de la ruta.
 *
 * El contenido se reenvía en streaming para no cargar archivos de hasta 75 MB en memoria.
 */
#[Route('/api/files', name: 'api_files_')]
class FileController extends AbstractController
{
    private const TOKEN = '[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+';

    /** Cabeceras de la respuesta de Spring que se devuelven al navegador. */
    private const PASSTHROUGH_HEADERS = [
        'content-type', 'content-length', 'content-disposition', 'cache-control', 'etag', 'x-content-type-options',
    ];

    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('/upload/{token}', name: 'upload', requirements: ['token' => self::TOKEN], methods: ['PUT'])]
    public function upload(string $token, Request $request): Response
    {
        $headers = ['Content-Type' => $request->headers->get('Content-Type', 'application/octet-stream')];
        if ($request->headers->has('Content-MD5')) {
            $headers['Content-MD5'] = $request->headers->get('Content-MD5');
        }
        if ($request->headers->has('Content-Length')) {
            $headers['Content-Length'] = $request->headers->get('Content-Length');
        }

        $springResponse = $this->springClient->request('PUT', "/files/upload/{$token}", [
            'headers' => $headers,
            'body' => $request->getContent(true),
        ]);

        return new Response(
            $springResponse->getContent(false),
            $springResponse->getStatusCode(),
            $this->passthroughHeaders($springResponse->getHeaders(false)),
        );
    }

    #[Route('/{token}', name: 'download', requirements: ['token' => self::TOKEN], methods: ['GET'])]
    public function download(string $token): Response
    {
        $springResponse = $this->springClient->request('GET', "/files/{$token}", [
            'headers' => ['Accept' => '*/*'],
        ]);
        $status = $springResponse->getStatusCode();
        $headers = $this->passthroughHeaders($springResponse->getHeaders(false));

        if ($status !== Response::HTTP_OK) {
            return new Response($springResponse->getContent(false), $status, $headers);
        }

        $client = $this->springClient;

        return new StreamedResponse(static function () use ($client, $springResponse): void {
            foreach ($client->stream($springResponse) as $chunk) {
                echo $chunk->getContent();
                flush();
            }
        }, $status, $headers);
    }

    /**
     * @param array<string, list<string>> $springHeaders
     *
     * @return array<string, string>
     */
    private function passthroughHeaders(array $springHeaders): array
    {
        $headers = [];
        foreach (self::PASSTHROUGH_HEADERS as $name) {
            if (isset($springHeaders[$name][0])) {
                $headers[$name] = $springHeaders[$name][0];
            }
        }

        return $headers;
    }
}
