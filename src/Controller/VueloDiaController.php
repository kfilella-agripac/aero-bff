<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vuelos-dia', name: 'api_vuelos_dia_')]
class VueloDiaController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        return $this->springClient->forward($request, '/vuelos-dia');
    }

    #[Route('/prefill/{flightScheduleId}', name: 'prefill', requirements: ['flightScheduleId' => '\d+'], methods: ['GET'])]
    public function prefill(int $flightScheduleId, Request $request): Response
    {
        return $this->springClient->forward($request, "/vuelos-dia/prefill/{$flightScheduleId}");
    }

    #[Route('/{id}/firma/{role}', name: 'firma', requirements: ['id' => '\d+', 'role' => 'mecanico|piloto'], methods: ['GET'])]
    public function firma(int $id, string $role, Request $request): Response
    {
        return $this->springClient->forward($request, "/vuelos-dia/{$id}/firma/{$role}");
    }

    #[Route('/{id}', name: 'item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function item(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/vuelos-dia/{$id}");
    }
}
