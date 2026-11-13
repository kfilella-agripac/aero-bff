<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Fase 5.4: controles de combustible y reporte de anomalías de consumo.
 * Los PDFs (/fuel-controls/{id}/pdf y /reports/operations/fuel-anomalies/pdf) llegan en la Fase 6.2.
 */
#[Route('/api', name: 'api_fuel_')]
class FuelControlController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('/fuel-controls', name: 'controls_index_store', methods: ['GET', 'POST'])]
    public function controls(Request $request): Response
    {
        return $this->springClient->forward($request, '/fuel-controls');
    }

    #[Route('/fuel-controls/eligible-flights', name: 'eligible_flights', methods: ['GET'])]
    public function eligibleFlights(Request $request): Response
    {
        return $this->springClient->forward($request, '/fuel-controls/eligible-flights');
    }

    #[Route('/fuel-controls/{id}', name: 'controls_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function control(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/fuel-controls/{$id}");
    }

    #[Route('/fuel-controls/{id}/restore', name: 'controls_restore', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function restore(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/fuel-controls/{$id}/restore");
    }

    #[Route('/fuel-controls/{id}/signature/{role}', name: 'controls_signature', requirements: ['id' => '\d+', 'role' => 'mechanic|pilot'], methods: ['GET'])]
    public function signature(int $id, string $role, Request $request): Response
    {
        return $this->springClient->forward($request, "/fuel-controls/{$id}/signature/{$role}");
    }

    #[Route('/reports/operations/fuel-anomalies', name: 'anomalies', methods: ['GET'])]
    public function anomalies(Request $request): Response
    {
        return $this->springClient->forward($request, '/reports/operations/fuel-anomalies');
    }
}
