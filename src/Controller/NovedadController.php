<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Fase 4.4: novedades y catálogo de tipos de novedad.
 * Novedades con adjuntos (multipart) dependen del reenvío multipart de SpringBackendClient.
 */
#[Route('/api', name: 'api_novedades_')]
class NovedadController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('/novedades', name: 'index_store', methods: ['GET', 'POST'])]
    public function novedades(Request $request): Response
    {
        return $this->springClient->forward($request, '/novedades');
    }

    #[Route('/novedades/{id}', name: 'item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function novedad(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/novedades/{$id}");
    }

    #[Route('/novedad-tipos', name: 'tipos_index_store', methods: ['GET', 'POST'])]
    public function tipos(Request $request): Response
    {
        return $this->springClient->forward($request, '/novedad-tipos');
    }

    #[Route('/novedad-tipos/{id}', name: 'tipos_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function tipo(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/novedad-tipos/{$id}");
    }
}
