# Fase 6: Streaming binario de reportes Excel/PDF y envío semanal en el BFF (`aero-bff`)

**Fecha:** 2026-11-19  
**Rama:** `fase-6`  
**Alcance:** Reenvío binario (streaming) de reportes `.xlsx`, `.pdf` y disparo del envío semanal hacia `SAeroPlanificacion`.

---

## 1. Cliente HTTP hacia Spring (`SpringBackendClient`)

- **`forward(...)`**: preservación de cabeceras `Content-Disposition` y `Cache-Control` en respuestas binarias o de descarga.
- **`forwardStream(...)`**: nuevo método para reenviar respuestas binarias (`application/pdf`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`) preservando `Content-Type`, `Content-Disposition` y `Cache-Control`.

## 2. Rutas expuestas en controladores

- **`ReportController`** (`/api/reports/...`):
  - `GET /planning`, `GET /planning/pistas`, `GET /tachometer-jumps`
  - `GET` y `POST /planning/pistas/excel`
  - `POST /operations/fuel-anomalies/pdf`
  - `POST /planning/pdf`
  - `POST /planning/pistas/pdf`
  - `POST /bitacora/pdf`
  - `POST /tachometer-jumps/pdf`
  - `POST /planning/send-weekly`
- **`FuelControlController`**: `GET /api/fuel-controls/{id}/pdf`
- **`FumigationOrderController`**: `GET /api/fumigation-orders/{id}/pdf`

## 3. Pruebas

- **`ReportControllerTest`**: cobertura PHPUnit sobre los endpoints de reportes JSON, streaming binario `.xlsx` / `.pdf` y envío semanal.
- **Ejecución verificada:** `make test-bff` (`16 tests, 123 assertions`) y pruebas E2E contra contenedores Docker (`aero-bff` -> `aero-backend-spring` -> `aero-mailpit`).
