<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_weekly_plannings_')]
class WeeklyPlanningController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('/flight-schedules/week/{week}/year/{year}/status', name: 'week_status', requirements: ['week' => '\d+', 'year' => '\d+'], methods: ['GET'])]
    public function weekStatus(int $week, int $year, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/week/{$week}/year/{$year}/status");
    }

    #[Route('/flight-schedules/approve-week', name: 'approve_week', methods: ['POST'])]
    public function approveWeek(Request $request): Response
    {
        return $this->springClient->forward($request, '/flight-schedules/approve-week');
    }

    #[Route('/flight-schedules/week/{week}/year/{year}/unlock', name: 'unlock_week', requirements: ['week' => '\d+', 'year' => '\d+'], methods: ['POST'])]
    public function unlockWeek(int $week, int $year, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/week/{$week}/year/{$year}/unlock");
    }

    #[Route('/flight-schedules/week/{week}/year/{year}/request-unlock', name: 'request_unlock', requirements: ['week' => '\d+', 'year' => '\d+'], methods: ['POST'])]
    public function requestUnlock(int $week, int $year, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/week/{$week}/year/{$year}/request-unlock");
    }

    #[Route('/flight-schedules/week/{week}/year/{year}/send-approval-email', name: 'send_approval_email', requirements: ['week' => '\d+', 'year' => '\d+'], methods: ['POST'])]
    public function sendApprovalEmail(int $week, int $year, Request $request): Response
    {
        return $this->springClient->forward($request, "/flight-schedules/week/{$week}/year/{$year}/send-approval-email");
    }

    #[Route('/weekly-plannings/unlock-requests', name: 'unlock_requests', methods: ['GET'])]
    public function unlockRequests(Request $request): Response
    {
        return $this->springClient->forward($request, '/weekly-plannings/unlock-requests');
    }

    #[Route('/unlock-requests/{id}', name: 'update_unlock_request', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[Route('/weekly-plannings/unlock-requests/{id}', name: 'update_weekly_unlock_request', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function updateUnlockRequest(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/unlock-requests/{$id}");
    }

    #[Route('/unlock-requests/{id}/resolve', name: 'resolve_unlock_request', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[Route('/weekly-plannings/unlock-requests/{id}/resolve', name: 'resolve_weekly_unlock_request', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function resolveUnlockRequest(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/unlock-requests/{$id}/resolve");
    }
}
