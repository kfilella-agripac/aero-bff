<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/weekly-plannings', name: 'api_weekly_plannings_')]
class WeeklyPlanningController extends AbstractController
{
    public function __construct(
        private SpringBackendClient $springClient
    ) {}

    #[Route('/unlock-requests/{id}/resolve', name: 'resolve_unlock', methods: ['POST'])]
    public function resolveUnlock(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/weekly-plannings/unlock-requests/{$id}/resolve");
    }
}
