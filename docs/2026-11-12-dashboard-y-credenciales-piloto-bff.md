# Proxy de Dashboard y Credenciales de Pilotos en BFF (Symfony)

**Fecha:** 2026-11-12

## Resumen de Cambios

Se expusieron en `aero-bff` los endpoints públicos para el Dashboard operativo y para el CRUD de licencias y certificados médicos de pilotos, delegando hacia `SAeroPlanificacion`.

### 1. Dashboard (`DashboardController`)
- `GET /api/dashboard/overview` -> `/api/internal/dashboard/overview`
- `GET /api/dashboard/expired-documents` -> `/api/internal/dashboard/expired-documents`
- `GET /api/dashboard/expiring-soon` -> `/api/internal/dashboard/expiring-soon`
- `GET /api/dashboard/weekly-summary/{year}/{week}` -> `/api/internal/dashboard/weekly-summary/{year}/{week}`

### 2. Licencias y Certificados Médicos de Pilotos (`CatalogController`)
- `GET /api/pilotos/{id}/licenses`
- `POST /api/pilotos/{id}/licenses`
- `PUT /api/pilotos/{id}/licenses/{licenseId}`
- `DELETE /api/pilotos/{id}/licenses/{licenseId}`
- `POST /api/pilotos/{id}/licenses/{licenseId}/restore`
- `GET /api/pilotos/{id}/medical-certificates`
- `POST /api/pilotos/{id}/medical-certificates`
- `PUT /api/pilotos/{id}/medical-certificates/{certId}`
- `DELETE /api/pilotos/{id}/medical-certificates/{certId}`
- `POST /api/pilotos/{id}/medical-certificates/{certId}/restore`

### 3. Pruebas (`DashboardControllerTest`)
- Se añadieron pruebas unitarias en PHPUnit para validar la delegación y respuesta JSON de los 4 endpoints del Dashboard.

## Verificación
- `make test-bff`: **9 tests, 47 assertions** exitosos.
- Verificación E2E con Playwright contra `http://localhost:8003/api`: confirmados `GET /api/dashboard/*`, `PUT /api/pilotos/1/medical-certificates/1`, `POST /api/pilotos/1/licenses` y `DELETE /api/pilotos/1/licenses/{id}`.
