<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    #[Route('/api/health', name: 'app_api_health', methods: ['GET'])]
    public function health(SpringBackendClient $springClient): JsonResponse
    {
        $springHealth = ['status' => 'UNKNOWN'];
        $springStatusCode = 500;

        try {
            $response = $springClient->request('GET', '/health', [
                'timeout' => 4.0,
            ]);
            $springStatusCode = $response->getStatusCode();
            $springHealth = $response->toArray(false);
        } catch (\Throwable $e) {
            $springHealth = [
                'status' => 'DOWN',
                'error' => $e->getMessage(),
            ];
        }

        $overallStatus = ($springStatusCode === 200 && ($springHealth['status'] ?? '') === 'UP') ? 'OK' : 'DEGRADED';

        return $this->json([
            'bff' => [
                'status' => 'UP',
                'service' => 'aero-bff',
                'gateway' => 'Symfony 7.4',
            ],
            'spring_backend' => $springHealth,
            'status' => $overallStatus,
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ], $overallStatus === 'OK' ? 200 : 503);
    }
}
