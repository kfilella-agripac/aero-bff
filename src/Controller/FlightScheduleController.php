<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/flight-schedules', name: 'api_flight_schedules_')]
class FlightScheduleController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        return $this->springClient->forward($request, '/flight-schedules');
    }

    #[Route('/next-code', name: 'next_code', methods: ['GET'])]
    public function nextCode(Request $request): Response
    {
        return $this->springClient->forward($request, '/flight-schedules/next-code');
    }

    #[Route('/set-next-code', name: 'set_next_code', methods: ['POST'])]
    public function setNextCode(Request $request): Response
    {
        return $this->springClient->forward($request, '/flight-schedules/set-next-code');
    }

    #[Route('/weekly/{year}/{week}', name: 'weekly', requirements: ['year' => '\d+', 'week' => '\d+'], methods: ['GET'])]
    public function weekly(int $year, int $week, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/weekly/{$year}/{$week}");
    }

    #[Route('/conflicts', name: 'conflicts', methods: ['GET', 'POST'])]
    public function conflicts(Request $request): Response
    {
        return $this->springClient->forward($request, '/flight-schedules/conflicts');
    }

    #[Route('/bulk', name: 'bulk', methods: ['POST'])]
    public function bulk(Request $request): Response
    {
        return $this->springClient->forward($request, '/flight-schedules/bulk');
    }

    #[Route('/{id}/assign', name: 'assign', requirements: ['id' => '\d+'], methods: ['PATCH', 'POST'])]
    public function assign(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/assign");
    }

    #[Route('/{id}/block', name: 'block', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function block(int $id, Request $request): Response
    {
        // Bloqueo con documentos de respaldo: multipart con `documents[]`.
        if (SpringBackendClient::isMultipart($request)) {
            return $this->springClient->forwardMultipart($request, "/flight-schedules/{$id}/block");
        }

        return $this->springClient->forward($request, "/flight-schedules/{$id}/block");
    }

    #[Route('/{id}/unblock', name: 'unblock', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function unblock(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/unblock");
    }

    #[Route('/{id}/validate', name: 'validate', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function validateSchedule(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/validate");
    }

    #[Route('/{id}/operation-status', name: 'operation_status', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function operationStatus(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/operation-status");
    }

    #[Route('/{id}/approve', name: 'approve', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function approveSchedule(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/approve");
    }

    #[Route('/{id}/sync-history', name: 'sync_history', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function syncHistory(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/sync-history");
    }

    #[Route('/{id}/syncs', name: 'syncs', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function syncs(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/syncs");
    }

    #[Route('/{id}/retry-sync', name: 'retry_sync', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function retrySync(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/retry-sync");
    }

    #[Route('/{id}/prevuelo', name: 'prevuelo', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function prevuelo(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/prevuelo");
    }

    #[Route('/{id}/flights/latest-tacometer', name: 'flights_latest_tacometer', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function latestTacometer(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/flights/latest-tacometer");
    }

    #[Route('/{id}/flights', name: 'flights', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function flights(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/flights");
    }

    #[Route('/{id}/vuelos-dia', name: 'vuelos_dia', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function vuelosDia(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/vuelos-dia");
    }

    #[Route('/{id}', name: 'item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function item(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}");
    }
}
