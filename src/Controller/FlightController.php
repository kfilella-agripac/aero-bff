<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/flights', name: 'api_flights_')]
class FlightController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('/{id}', name: 'item', requirements: ['id' => '\d+'], methods: ['PUT', 'DELETE'])]
    public function item(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/flights/{$id}");
    }
}
