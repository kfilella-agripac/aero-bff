# Fase 2: Controladores de Planificación Semanal y Conflictos en BFF Symfony

**Fecha:** 2026-10-22  
**Módulo:** `aero-bff` (Symfony 7.4 / PHP 8.4.27)  
**Estado:** Implementado y Verificado  

---

## 1. Resumen
Se implementaron los controladores en el BFF Gateway para exponer la API pública de planificación semanal, detección de conflictos y asignación de vuelos para `aero-front` y `aero-app`:

- `FlightScheduleController`:
  - `GET /api/flight-schedules/next-code`: Consulta el siguiente código secuencial disponible (`FS-YYYY-XXXX`).
  - `GET / POST /api/flight-schedules/conflicts`: Verificación de colisiones operativas de aeronave o piloto antes de agendar.
  - `GET /api/flight-schedules/weekly/{year}/{week}`: Consulta de vuelos programados para la semana y pista.
  - `GET /api/flight-schedules/week/{week}/year/{year}/status`: Estado de bloqueo y aprobación semanal.
  - `POST /api/flight-schedules/approve-week`: Aprobación semanal de vuelos.
  - `POST /api/flight-schedules/week/{week}/year/{year}/request-unlock`: Solicitud de desbloqueo temporal de semana.
  - `GET / POST / PUT / DELETE /api/flight-schedules`: Operaciones CRUD de programación de vuelos.
  - `PATCH /api/flight-schedules/{id}/assign`: Asignación rápida de piloto y aeronave.

- `WeeklyPlanningController`:
  - `POST /api/weekly-plannings/unlock-requests/{id}/resolve`: Resolución de solicitudes de desbloqueo por parte de administradores.

---

## 2. Pruebas Funcionales
Se añadieron casos de prueba en `tests/Controller/FlightScheduleControllerTest.php` validando los endpoints `/next-code`, `/weekly/...` y `/week/.../status` a través del gateway BFF sobre PHP 8.4.27 (5 de 5 pruebas exitosas).
