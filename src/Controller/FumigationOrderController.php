<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Fase 4.4: órdenes de fumigación y cuentas fitosanitarias.
 * Los permisos por rol los aplica Spring; el PDF de la orden llega en la Fase 6.2.
 */
#[Route('/api', name: 'api_fumigation_')]
class FumigationOrderController extends AbstractController
{
    public function __construct(
        private readonly SpringBackendClient $springClient,
    ) {
    }

    #[Route('/fumigation-orders', name: 'orders_index_store', methods: ['GET', 'POST'])]
    public function orders(Request $request): Response
    {
        return $this->springClient->forward($request, '/fumigation-orders');
    }

    #[Route('/fumigation-orders/{id}', name: 'orders_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function order(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/fumigation-orders/{$id}");
    }

    #[Route('/fitosanitarios', name: 'fitosanitarios_index_store', methods: ['GET', 'POST'])]
    public function fitosanitarios(Request $request): Response
    {
        return $this->springClient->forward($request, '/fitosanitarios');
    }

    #[Route('/fitosanitarios/{id}', name: 'fitosanitarios_item', requirements: ['id' => '\d+'], methods: ['PUT', 'DELETE'])]
    public function fitosanitario(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/fitosanitarios/{$id}");
    }
}
