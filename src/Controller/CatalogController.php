<?php

namespace App\Controller;

use App\Service\SpringBackendClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_catalog_')]
class CatalogController extends AbstractController
{
    public function __construct(
        private SpringBackendClient $springClient
    ) {}

    // =========================================================================
    // PISTAS
    // =========================================================================
    #[Route('/pistas', name: 'pistas_index_store', methods: ['GET', 'POST'])]
    public function pistas(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/pistas');
    }

    #[Route('/pistas/active', name: 'pistas_active', methods: ['GET'])]
    public function pistasActive(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/pistas/active');
    }

    #[Route('/pistas/{id}', name: 'pistas_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function pistaItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/pistas/{$id}");
    }

    // =========================================================================
    // CLIENTES
    // =========================================================================
    #[Route('/clientes', name: 'clientes_index_store', methods: ['GET', 'POST'])]
    public function clientes(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/clientes');
    }

    #[Route('/clientes/{id}', name: 'clientes_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function clienteItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/clientes/{$id}");
    }

    #[Route('/clientes/{id}/restore', name: 'clientes_restore', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function clienteRestore(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/clientes/{$id}/restore");
    }

    // =========================================================================
    // ZONAS
    // =========================================================================
    #[Route('/zonas', name: 'zonas_index_store', methods: ['GET', 'POST'])]
    public function zonas(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/zonas');
    }

    #[Route('/zonas/{id}', name: 'zonas_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function zonaItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/zonas/{$id}");
    }

    // =========================================================================
    // FINCAS
    // =========================================================================
    #[Route('/fincas', name: 'fincas_index_store', methods: ['GET', 'POST'])]
    public function fincas(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/fincas');
    }

    #[Route('/fincas/{id}', name: 'fincas_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function fincaItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/fincas/{$id}");
    }

    #[Route('/fincas/{fincaId}/sectors', name: 'fincas_sectors', requirements: ['fincaId' => '\d+'], methods: ['GET'])]
    public function fincaSectors(int $fincaId, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/fincas/{$fincaId}/sectors");
    }

    // =========================================================================
    // SECTORES & FINCA_SECTORES
    // =========================================================================
    #[Route('/sectors', name: 'sectors_index_store', methods: ['GET', 'POST'])]
    #[Route('/sectores', name: 'sectores_index_store', methods: ['GET', 'POST'])]
    public function sectores(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/sectores');
    }

    #[Route('/sectors/{id}', name: 'sectors_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    #[Route('/sectores/{id}', name: 'sectores_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function sectorItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/sectores/{$id}");
    }

    #[Route('/finca-sectors', name: 'finca_sectors_index_store', methods: ['GET', 'POST'])]
    #[Route('/finca-sectores', name: 'finca_sectores_index_store', methods: ['GET', 'POST'])]
    public function fincaSectores(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/finca-sectores');
    }

    #[Route('/finca-sectors/{id}', name: 'finca_sectors_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    #[Route('/finca-sectores/{id}', name: 'finca_sectores_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function fincaSectorItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/finca-sectores/{$id}");
    }

    // =========================================================================
    // AERONAVES
    // =========================================================================
    #[Route('/aeronaves', name: 'aeronaves_index_store', methods: ['GET', 'POST'])]
    public function aeronaves(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/aeronaves');
    }

    #[Route('/aeronaves/{id}', name: 'aeronaves_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function aeronaveItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/aeronaves/{$id}");
    }

    #[Route('/aeronaves/{id}/tachometer', name: 'aeronaves_tachometer', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function aeronaveTachometer(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/aeronaves/{$id}/tachometer");
    }

    #[Route('/aeronaves/{id}/validate-documents', name: 'aeronaves_validate_docs', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function aeronaveValidateDocuments(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/aeronaves/{$id}/validate-documents");
    }

    #[Route('/aeronaves/{id}/restore', name: 'aeronaves_restore', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function aeronaveRestore(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/aeronaves/{$id}/restore");
    }

    #[Route('/aeronaves/{id}/force', name: 'aeronaves_force_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function aeronaveForceDelete(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/aeronaves/{$id}/force");
    }

    // =========================================================================
    // PILOTOS, MECÁNICOS & JEFES DE PISTA
    // =========================================================================
    #[Route('/pilotos', name: 'pilotos_index_store', methods: ['GET', 'POST'])]
    public function pilotos(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/pilotos');
    }

    #[Route('/pilotos/available-for-aircraft/{aeronaveId}', name: 'pilotos_available_aircraft', requirements: ['aeronaveId' => '\d+'], methods: ['GET'])]
    public function pilotosAvailableForAircraft(int $aeronaveId, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/pilotos/available-for-aircraft/{$aeronaveId}");
    }

    #[Route('/pilotos/{id}', name: 'pilotos_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function pilotoItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/pilotos/{$id}");
    }

    #[Route('/pilotos/{id}/validate-documents', name: 'pilotos_validate_docs', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function pilotoValidateDocuments(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/pilotos/{$id}/validate-documents");
    }

    #[Route('/pilotos/{id}/tachometer-limits', name: 'pilotos_tachometer_limits', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function pilotoTachometerLimits(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/pilotos/{$id}/tachometer-limits");
    }

    #[Route('/pilotos/{id}/licenses', name: 'pilotos_licenses', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function pilotoLicenses(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/pilotos/{$id}/licenses");
    }

    #[Route('/pilotos/{id}/medical-certificates', name: 'pilotos_medical_certificates', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function pilotoMedicalCertificates(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/pilotos/{$id}/medical-certificates");
    }

    #[Route('/pilotos/{id}/restore', name: 'pilotos_restore', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function pilotoRestore(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/pilotos/{$id}/restore");
    }

    #[Route('/pilotos/{id}/force', name: 'pilotos_force_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function pilotoForceDelete(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/pilotos/{$id}/force");
    }

    #[Route('/mecanicos', name: 'mecanicos_index_store', methods: ['GET', 'POST'])]
    public function mecanicos(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/mecanicos');
    }

    #[Route('/mecanicos/{id}', name: 'mecanicos_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function mecanicoItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/mecanicos/{$id}");
    }

    #[Route('/jefes-pista', name: 'jefes_pista_index_store', methods: ['GET', 'POST'])]
    public function jefesPista(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/jefes-pista');
    }

    #[Route('/jefes-pista/{id}', name: 'jefes_pista_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function jefePistaItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/jefes-pista/{$id}");
    }

    // =========================================================================
    // SETTINGS & CYCLE FACTORS
    // =========================================================================
    #[Route('/settings', name: 'settings_index', methods: ['GET'])]
    public function settings(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/settings');
    }

    #[Route('/settings/cycle-factors', name: 'settings_cycle_factors', methods: ['GET'])]
    public function cycleFactors(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/settings/cycle-factors');
    }

    #[Route('/settings/calculate-cycle', name: 'settings_calculate_cycle', methods: ['POST'])]
    public function calculateCycle(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/settings/calculate-cycle');
    }

    #[Route('/settings/{key}', name: 'settings_update', methods: ['PUT'])]
    public function updateSetting(string $key, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/settings/{$key}");
    }

    // =========================================================================
    // PRODUCTS (FASE 2)
    // =========================================================================
    #[Route('/products', name: 'products_index', methods: ['GET', 'POST'])]
    public function products(Request $request): Response
    {
        return $this->springClient->forward($request, '/catalogs/products');
    }

    #[Route('/products/{id}/restore', name: 'products_restore', methods: ['POST'])]
    public function restoreProduct(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/products/{$id}/restore");
    }

    #[Route('/products/{id}', name: 'products_item', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'DELETE'])]
    public function productItem(int $id, Request $request): Response
    {
        return $this->springClient->forward($request, "/catalogs/products/{$id}");
    }
}
