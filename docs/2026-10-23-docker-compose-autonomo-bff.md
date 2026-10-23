# Despliegue Autónomo con Docker Compose para Aero-BFF

**Fecha:** 2026-10-23  
**Repositorio:** `aero-bff`  
**Tecnología:** Symfony 7.4 / PHP 8.4 CLI (parche 8.4.27)

---

## 1. Objetivo

Permitir que cualquier desarrollador que trabaje en la capa BFF (Symfony) pueda levantar el entorno de desarrollo de forma aislada y autónoma mediante Docker, sin depender del repositorio raíz ni de la infraestructura global.

---

## 2. Componentes Implementados

### A. `compose.yaml` (Docker Compose Autónomo)
* **Servicio `bff`:**
  * Basado en el `Dockerfile` de PHP 8.4.27 con Composer 2 preinstalado.
  * Volumen montado en `/var/www/html` con persistencia de dependencias en el volumen `aero_bff_vendor`.
  * Parámetro `extra_hosts` con `host.docker.internal:host-gateway` para comunicarse de forma transparente con instancias de Spring Boot que corran en la máquina host.
  * Puerto por defecto: `8000:8000` (configurable mediante `BFF_PORT`).
  * Healthcheck integrado apuntando a `/api/health`.

### B. `.env.example`
* Plantilla documentada con las variables críticas de configuración:
  * `BFF_PORT`: Puerto de escucha del API Gateway.
  * `SPRING_BACKEND_URL`: URL base para llamadas REST hacia Spring Boot Core (`/api/internal`).
  * `SPRING_INTERNAL_SECRET`: Token de autenticación inter-servicios (`X-Internal-Secret`).
  * `CORS_ALLOW_ORIGIN`: Reglas de CORS para permitir solicitudes desde el Frontend web (Vue 3).

### C. `README.md`
* Guía de inicio rápido para ejecución con Docker y sin Docker.
