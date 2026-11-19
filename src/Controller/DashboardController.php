<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard', name: 'api_dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('/overview', name: 'overview', methods: ['GET'])]
    public function overview(Request $request): Response
    {
        return $this->springClient->forward($request, '/dashboard/overview');
    }

    #[Route('/expired-documents', name: 'expired_documents', methods: ['GET'])]
    public function expiredDocuments(Request $request): Response
    {
        return $this->springClient->forward($request, '/dashboard/expired-documents');
    }

    #[Route('/expiring-soon', name: 'expiring_soon', methods: ['GET'])]
    public function expiringSoon(Request $request): Response
    {
        return $this->springClient->forward($request, '/dashboard/expiring-soon');
    }

    #[Route('/weekly-summary/{year}/{week}', name: 'weekly_summary', requirements: ['year' => '\d+', 'week' => '\d+'], methods: ['GET'])]
    public function weeklySummary(int $year, int $week, Request $request): Response
    {
        return $this->springClient->forward($request, "/dashboard/weekly-summary/{$year}/{$week}");
    }
}
