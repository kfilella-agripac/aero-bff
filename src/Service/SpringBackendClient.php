<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

        // Pasar headers de autenticación del cliente si existen
        foreach (['authorization', 'x-api-token'] as $authHeader) {
            if ($incomingRequest->headers->has($authHeader)) {
                $options['headers'][$authHeader] = $incomingRequest->headers->get($authHeader);
            }
        }

        // Si es POST/PUT/PATCH, reenviar body
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $contentType = $incomingRequest->headers->get('Content-Type', '');
            if (str_contains($contentType, 'application/json')) {
                $options['body'] = $incomingRequest->getContent();
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
