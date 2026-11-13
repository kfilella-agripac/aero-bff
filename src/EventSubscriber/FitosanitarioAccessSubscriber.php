<?php

namespace App\EventSubscriber;

use App\Security\TokenRoleResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Lista permitida para el rol fitosanitario (equivale al middleware FitosanitarioAccess de
 * aero-back): las rutas operativas existentes no quedan disponibles por defecto para este rol.
 * Cualquier ruta /api fuera de la lista responde 403.
 */
class FitosanitarioAccessSubscriber implements EventSubscriberInterface
{
    public const ROLE = 'fitosanitario';

    /** @var array<string, list<string>> método => patrones de ruta (sin el prefijo /api) */
    private const ALLOWED = [
        'GET' => [
            'me', 'user', 'fincas', 'clientes', 'zonas', 'sectors', 'products', 'products/\d+',
            'fincas/\d+/sectors', 'fumigation-orders', 'fumigation-orders/\d+', 'fumigation-orders/\d+/pdf',
        ],
        'POST' => ['logout', 'fumigation-orders', 'products'],
        'PUT' => ['fumigation-orders/\d+', 'products/\d+'],
        'PATCH' => ['products/\d+'],
        'DELETE' => ['fumigation-orders/\d+'],
    ];

    private const PUBLIC_PATHS = ['/api/health', '/api/login'];

    /** Archivos con URL firmada: los autoriza el token de la ruta, no el rol (p. ej. su propia firma). */
    private const SIGNED_PREFIX = '/api/files/';

    public function __construct(
        private readonly TokenRoleResolver $roleResolver,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Después del router (32) y antes del controlador.
        return [KernelEvents::REQUEST => ['onKernelRequest', 16]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();
        $path = rtrim($request->getPathInfo(), '/');
        if ($request->isMethod('OPTIONS') || !str_starts_with($path, '/api/') || in_array($path, self::PUBLIC_PATHS, true)
            || str_starts_with($path, self::SIGNED_PREFIX)) {
            return;
        }

        if ($this->roleResolver->resolve($request->headers->get('Authorization')) !== self::ROLE) {
            return;
        }

        if (!self::isAllowed($request->getMethod(), substr($path, strlen('/api/')))) {
            $event->setResponse(new JsonResponse(
                ['message' => 'El usuario fitosanitario no tiene acceso a esta acción.'],
                JsonResponse::HTTP_FORBIDDEN,
            ));
        }
    }

    public static function isAllowed(string $method, string $route): bool
    {
        foreach (self::ALLOWED[strtoupper($method)] ?? [] as $pattern) {
            if (preg_match('#^' . $pattern . '$#', $route)) {
                return true;
            }
        }

        return false;
    }
}
