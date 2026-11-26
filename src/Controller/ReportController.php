<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/reports', name: 'api_reports_')]
class ReportController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('/planning', name: 'planning', methods: ['GET'])]
    public function planning(Request $request): Response
    {
        return $this->springClient->forward($request, '/reports/planning');
    }

    #[Route('/planning/pistas', name: 'planning_pistas', methods: ['GET'])]
    public function planningPistas(Request $request): Response
    {
        return $this->springClient->forward($request, '/reports/planning/pistas');
    }

    #[Route('/planning/pistas/excel', name: 'planning_pistas_excel', methods: ['GET', 'POST'])]
    public function planningPistasExcel(Request $request): Response
    {
        return $this->springClient->forwardStream($request, '/reports/planning/pistas/excel');
    }

    #[Route('/tachometer-jumps', name: 'tachometer_jumps', methods: ['GET'])]
    public function tachometerJumps(Request $request): Response
    {
        return $this->springClient->forward($request, '/reports/tachometer-jumps');
    }

    #[Route('/planning/pdf', name: 'planning_pdf', methods: ['POST'])]
    public function planningPdf(Request $request): Response
    {
        return $this->springClient->forwardStream($request, '/reports/planning/pdf');
    }

    #[Route('/planning/pistas/pdf', name: 'planning_pistas_pdf', methods: ['POST'])]
    public function planningPistasPdf(Request $request): Response
    {
        return $this->springClient->forwardStream($request, '/reports/planning/pistas/pdf');
    }

    #[Route('/bitacora/pdf', name: 'bitacora_pdf', methods: ['POST'])]
    public function bitacoraPdf(Request $request): Response
    {
        return $this->springClient->forwardStream($request, '/reports/bitacora/pdf');
    }

    #[Route('/tachometer-jumps/pdf', name: 'tachometer_jumps_pdf', methods: ['POST'])]
    public function tachometerJumpsPdf(Request $request): Response
    {
        return $this->springClient->forwardStream($request, '/reports/tachometer-jumps/pdf');
    }

    #[Route('/planning/send-weekly', name: 'planning_send_weekly', methods: ['POST'])]
    public function sendWeekly(Request $request): Response
    {
        return $this->springClient->forward($request, '/reports/planning/send-weekly');
    }
}
