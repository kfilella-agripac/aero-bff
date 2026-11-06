<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Fase 4.4: documentos polimórficos. El archivo viaja del navegador directo a S3 con la
 * URL prefirmada; el BFF solo reenvía JSON.
 */
#[Route('/api/documents', name: 'api_documents_')]
class DocumentController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->springClient->forward($request, '/documents');
    }

    #[Route('/presigned-url', name: 'presigned_url', methods: ['POST'])]
    public function presignedUrl(Request $request): Response
    {
        return $this->springClient->forward($request, '/documents/presigned-url');
    }

    #[Route('/complete-upload', name: 'complete_upload', methods: ['POST'])]
    public function completeUpload(Request $request): Response
    {
        return $this->springClient->forward($request, '/documents/complete-upload');
    }

    #[Route('/{id}', name: 'item', requirements: ['id' => '\d+'], methods: ['GET', 'DELETE'])]
    public function item(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/documents/{$id}");
    }

    #[Route('/{id}/file', name: 'file', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function download(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/documents/{$id}/file");
    }
}
