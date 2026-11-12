<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/documents', name: 'api_documents_')]
final class DocumentController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->springClient->forward($request, '/documents');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/documents/{$id}");
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function destroy(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/documents/{$id}");
    }
}
