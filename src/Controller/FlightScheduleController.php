<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/flight-schedules', name: 'api_flight_schedules_')]
class FlightScheduleController extends AbstractController
{
    public function __construct(
        private SpringBackendClient $springClient
    ) {}

    #[Route('/next-code', name: 'next_code', methods: ['GET'])]
    public function nextCode(Request $request): Response
    {
        return $this->springClient->forward($request, '/flight-schedules/next-code');
    }

    #[Route('/conflicts', name: 'conflicts', methods: ['GET', 'POST'])]
    public function conflicts(Request $request): Response
    {
        return $this->springClient->forward($request, '/flight-schedules/conflicts');
    }

    #[Route('/weekly/{year}/{week}', name: 'weekly', methods: ['GET'])]
    public function weekly(int $year, int $week, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/weekly/{$year}/{$week}");
    }

    #[Route('/week/{week}/year/{year}/status', name: 'week_status', methods: ['GET'])]
    public function weekStatus(int $week, int $year, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/week/{$week}/year/{$year}/status");
    }

    #[Route('/approve-week', name: 'approve_week', methods: ['POST'])]
    public function approveWeek(Request $request): Response
    {
        return $this->springClient->forward($request, '/flight-schedules/approve-week');
    }

    #[Route('/week/{week}/year/{year}/request-unlock', name: 'request_unlock', methods: ['POST'])]
    public function requestUnlock(int $week, int $year, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/week/{$week}/year/{$year}/request-unlock");
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}");
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        return $this->springClient->forward($request, '/flight-schedules');
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}");
    }

    #[Route('/{id}/assign', name: 'assign', methods: ['PATCH', 'POST'])]
    public function assign(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}/assign");
    }

    #[Route('/{id}', name: 'destroy', methods: ['DELETE'])]
    public function destroy(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/{$id}");
    }
}
