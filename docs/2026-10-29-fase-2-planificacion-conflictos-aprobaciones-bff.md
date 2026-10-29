# Fase 2: Endpoints de Planificación Semanal, Conflictos y Desbloqueos en BFF Symfony

**Fecha:** 29 de octubre de 2026  
**Rama:** `fase-2`  
**Tareas cubiertas:** 2.5

## Resumen
Se implementaron en `aero-bff` (Symfony 7.4) todos los controladores proxy de la Fase 2 para exponer a `aero-front` y `aero-app` las rutas de planificación semanal, generador de códigos, validación de conflictos en tiempo real, ciclo de aprobación semanal y solicitudes de desbloqueo, delegando la lógica transaccional hacia `SAeroPlanificacion` (Spring Boot).

## Controladores Implementados

### 1. `FlightScheduleController` (`/api/flight-schedules`)
- `GET`, `POST /api/flight-schedules`: Listado paginado/filtrado y creación de programaciones de vuelo.
- `GET /api/flight-schedules/next-code` y `POST /api/flight-schedules/set-next-code`: Consulta y configuración del secuencial `FS-YYYY-XXXX`.
- `GET /api/flight-schedules/weekly/{year}/{week}`: Vista semanal agrupada con estado de planificación y última solicitud de desbloqueo.
- `GET`, `POST /api/flight-schedules/conflicts`: Detector de conflictos de piloto y aeronave en tiempo real.
- `POST /api/flight-schedules/bulk`: Creación masiva de vuelos programados.
- `PATCH`, `POST /api/flight-schedules/{id}/assign`: Asignación rápida de piloto y aeronave.
- `POST /api/flight-schedules/{id}/block` y `/{id}/unblock`: Bloqueo y desbloqueo individual.
- `GET /api/flight-schedules/{id}/validate` y `/{id}/operation-status`: Validación documental y estado de autorización operativa.
- `GET`, `POST /api/flight-schedules/{id}/approve`: Aprobación individual de programación.
- `GET`, `PUT`, `DELETE /api/flight-schedules/{id}`: Consulta, edición y eliminación de programación.

### 2. `WeeklyPlanningController` (`/api`)
- `GET /api/flight-schedules/week/{week}/year/{year}/status`: Estado de bloqueo/aprobación semanal.
- `POST /api/flight-schedules/approve-week`: Aprobación y cierre de semana.
- `POST /api/flight-schedules/week/{week}/year/{year}/unlock`: Desbloqueo administrativo directo.
- `POST /api/flight-schedules/week/{week}/year/{year}/request-unlock`: Creación de solicitud de desbloqueo por jefe de pista.
- `GET /api/weekly-plannings/unlock-requests`: Listado de solicitudes de desbloqueo por estado.
- `PUT /api/unlock-requests/{id}` y `/api/weekly-plannings/unlock-requests/{id}`: Edición de solicitud pendiente.
- `POST /api/unlock-requests/{id}/resolve` y `/api/weekly-plannings/unlock-requests/{id}/resolve`: Aprobación o rechazo de solicitud de desbloqueo.

### 3. `CatalogController` — Extensión Catálogo de Productos
- `GET`, `POST /api/products`, `GET`, `PUT`, `DELETE /api/products/{id}` y `POST /api/products/{id}/restore`.

## Pruebas Automatizadas
- `FlightScheduleControllerTest`: Verifica el reenvío de `/api/flight-schedules/weekly/{year}/{week}` y `/api/flight-schedules/conflicts` mediante `MockHttpClient`.
