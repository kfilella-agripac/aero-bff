<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

class SpringBackendClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private RequestStack $requestStack,
        private Security $security,
        private string $springBackendUrl,
        private string $internalSecret
    ) {}

    /**
     * Realiza una petición HTTP hacia el backend interno de Spring Boot.
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $headers = $options['headers'] ?? [];
        $headers['X-Internal-Secret'] = $this->internalSecret;

        if (!isset($headers['Accept'])) {
            $headers['Accept'] = 'application/json';
        }

        $user = $this->security->getUser();
        if ($user) {
            $headers['X-User-Id'] = (string) $user->getUserIdentifier();
            $headers['X-User-Role'] = implode(',', $user->getRoles());
        }

        $options['headers'] = $headers;
        $url = rtrim($this->springBackendUrl, '/') . '/' . ltrim($uri, '/');

        return $this->httpClient->request($method, $url, $options);
    }

    /**
     * Reenvía una petición multipart/form-data (campos + archivos) a Spring.
     *
     * PHP ya parseó el body en $request->request y $request->files, por lo que getContent()
     * viene vacío: se reconstruye el multipart. Los arrays se envían como `campo[]`
     * (p. ej. `documents[]`, como lo manda aero-front) y los archivos se leen por partes.
     */
    public function forwardMultipart(Request $incomingRequest, string $targetPath): Response
    {
        $boundary = 'saero-' . bin2hex(random_bytes(12));
        $fields = self::flatten($incomingRequest->request->all());
        $files = self::flatten($incomingRequest->files->all());

        $body = (static function () use ($boundary, $fields, $files): \Generator {
            foreach ($fields as [$name, $value]) {
                yield "--{$boundary}\r\nContent-Disposition: form-data; name=\"" . self::quote($name) . "\"\r\n\r\n"
                    . $value . "\r\n";
            }
            foreach ($files as [$name, $file]) {
                if (!$file instanceof UploadedFile || !$file->isValid()) {
                    continue;
                }
                $type = $file->getClientMimeType() ?: 'application/octet-stream';
                yield "--{$boundary}\r\nContent-Disposition: form-data; name=\"" . self::quote($name)
                    . '"; filename="' . self::quote($file->getClientOriginalName()) . "\"\r\n"
                    . "Content-Type: {$type}\r\n\r\n";
                $handle = fopen($file->getPathname(), 'rb');
                try {
                    while (!feof($handle)) {
                        yield fread($handle, 65536);
                    }
                } finally {
                    fclose($handle);
                }
                yield "\r\n";
            }
            yield "--{$boundary}--\r\n";
        })();

        $options = ['headers' => ['Content-Type' => "multipart/form-data; boundary={$boundary}"], 'body' => $body];
        if ($incomingRequest->query->count() > 0) {
            $options['query'] = $incomingRequest->query->all();
        }
        foreach (['authorization', 'x-api-token', 'x-user-id', 'x-user-role'] as $authHeader) {
            if ($incomingRequest->headers->has($authHeader)) {
                $options['headers'][$authHeader] = $incomingRequest->headers->get($authHeader);
            }
        }

        $springResponse = $this->request($incomingRequest->getMethod(), $targetPath, $options);
        $headers = [];
        $respHeaders = $springResponse->getHeaders(false);
        if (isset($respHeaders['content-type'][0])) {
            $headers['Content-Type'] = $respHeaders['content-type'][0];
        }

        return new Response($springResponse->getContent(false), $springResponse->getStatusCode(), $headers);
    }

    public static function isMultipart(Request $request): bool
    {
        return str_starts_with(strtolower((string) $request->headers->get('Content-Type')), 'multipart/form-data');
    }

    /**
     * Aplana campos anidados a pares [nombre, valor]: ['documents' => [a, b]] => documents[] a, documents[] b.
     *
     * @return list<array{0: string, 1: mixed}>
     */
    private static function flatten(array $values, string $prefix = ''): array
    {
        $pairs = [];
        foreach ($values as $key => $value) {
            $name = $prefix === '' ? (string) $key : $prefix . (is_int($key) ? '[]' : "[{$key}]");
            if (is_array($value)) {
                array_push($pairs, ...self::flatten($value, $name));
            } else {
                $pairs[] = [$name, $value];
            }
        }

        return $pairs;
    }

    private static function quote(string $value): string
    {
        return str_replace(['"', "\r", "\n"], ['%22', '', ''], $value);
    }

    /**
     * Lee la respuesta de Spring por partes (descargas de archivos grandes).
     *
     * @return iterable<\Symfony\Contracts\HttpClient\ChunkInterface>
     */
    public function stream(ResponseInterface $response): iterable
    {
        return $this->httpClient->stream($response);
    }

    /**
     * Reenvía una petición HTTP completa y devuelve un Symfony HttpFoundation Response.
     */
    public function forward(Request $incomingRequest, string $targetPath, array $extraOptions = []): Response
    {
        $method = $incomingRequest->getMethod();
        $options = $extraOptions;

        // Pasar query params si existen
        if ($incomingRequest->query->count() > 0 && !isset($options['query'])) {
            $options['query'] = $incomingRequest->query->all();
        }

        // Pasar headers de autenticación e identidad del cliente si existen
        foreach (['authorization', 'x-api-token', 'x-user-id', 'x-user-role'] as $authHeader) {
            if ($incomingRequest->headers->has($authHeader)) {
                $options['headers'][$authHeader] = $incomingRequest->headers->get($authHeader);
            }
        }

        // Si es POST/PUT/PATCH/DELETE, reenviar body JSON si aplica
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $rawContent = $incomingRequest->getContent();
            if ($rawContent !== '') {
                $options['body'] = $rawContent;
                $options['headers']['Content-Type'] = 'application/json';
            }
        }

        $springResponse = $this->request($method, $targetPath, $options);
        $statusCode = $springResponse->getStatusCode();
        $content = $springResponse->getContent(false);

        $headers = [];
        $respHeaders = $springResponse->getHeaders(false);
        if (isset($respHeaders['content-type'][0])) {
            $headers['Content-Type'] = $respHeaders['content-type'][0];
        }

        return new Response($content, $statusCode, $headers);
    }
}
