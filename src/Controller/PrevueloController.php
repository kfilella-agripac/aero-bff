<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/prevuelos', name: 'api_prevuelos_')]
class PrevueloController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        return $this->springClient->forward($request, '/prevuelos');
    }

    #[Route('/available-schedules', name: 'available_schedules', methods: ['GET'])]
    public function availableSchedules(Request $request): Response
    {
        return $this->springClient->forward($request, '/prevuelos/available-schedules');
    }

    #[Route('/all-scheduled-flights', name: 'all_scheduled_flights', methods: ['GET'])]
    public function allScheduledFlights(Request $request): Response
    {
        return $this->springClient->forward($request, '/prevuelos/all-scheduled-flights');
    }

    #[Route('/next-tachometer', name: 'next_tachometer', methods: ['GET'])]
    public function nextTachometer(Request $request): Response
    {
        return $this->springClient->forward($request, '/prevuelos/next-tachometer');
    }

    #[Route('/{aircraftType}/{id}/date', name: 'update_date', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function updateDate(string $aircraftType, int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/prevuelos/{$aircraftType}/{$id}/date");
    }

    #[Route('/{aircraftType}/{id}', name: 'item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function item(string $aircraftType, int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/prevuelos/{$aircraftType}/{$id}");
    }
}
