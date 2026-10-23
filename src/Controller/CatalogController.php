<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_catalog_')]
class CatalogController extends AbstractController
{
    public function __construct(
        private SpringBackendClient $springClient
    ) {}

    #[Route('/pistas', name: 'pistas', methods: ['GET'])]
    public function pistas(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/pistas');
    }

    #[Route('/clientes', name: 'clientes', methods: ['GET'])]
    public function clientes(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/clientes');
    }

    #[Route('/zonas', name: 'zonas', methods: ['GET'])]
    public function zonas(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/zonas');
    }

    #[Route('/fincas', name: 'fincas', methods: ['GET'])]
    public function fincas(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/fincas');
    }

    #[Route('/sectors', name: 'sectors', methods: ['GET'])]
    #[Route('/sectores', name: 'sectores', methods: ['GET'])]
    public function sectores(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/sectores');
    }

    #[Route('/finca-sectors', name: 'finca_sectors', methods: ['GET'])]
    public function fincaSectores(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/finca-sectors');
    }

    #[Route('/aeronaves', name: 'aeronaves', methods: ['GET'])]
    public function aeronaves(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/aeronaves');
    }

    #[Route('/pilotos', name: 'pilotos', methods: ['GET'])]
    public function pilotos(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/pilotos');
    }

    #[Route('/mecanicos', name: 'mecanicos', methods: ['GET'])]
    public function mecanicos(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/mecanicos');
    }

    #[Route('/settings', name: 'settings', methods: ['GET'])]
    public function settings(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/settings');
    }
}
