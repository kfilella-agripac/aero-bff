<?php

namespace App\EventSubscriber;

use App\Security\TokenRoleResolver;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Exige un token Bearer válido en todos los endpoints /api/* excepto rutas públicas
 * (/api/health, /api/login, /api/files/*) y preflight CORS (OPTIONS).
 */
class BearerAuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenRoleResolver $tokenRoleResolver,
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 24]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($this->environment === 'test' && !$request->headers->has('X-Test-Enforce-Auth')) {
            return;
        }

        if ($request->isMethod('OPTIONS')) {
            return;
        }

        $path = $request->getPathInfo();
        if (!str_starts_with($path, '/api')) {
            return;
        }

        if (
            $path === '/api/health'
            || $path === '/api/login'
            || str_starts_with($path, '/api/files/')
        ) {
            return;
        }

        $role = $this->tokenRoleResolver->resolve($request->headers->get('Authorization'));
        if ($role === null) {
            $event->setResponse(new JsonResponse(['message' => 'No autenticado.'], 401));
        }
    }
}
