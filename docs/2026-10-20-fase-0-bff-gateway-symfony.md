# Aero-BFF - Fase 0: Inicialización del API Gateway Symfony

**Fecha:** 2026-10-20  
**Estado:** Completado  
**Módulo:** `aero-bff`

---

## 1. Descripción

Creación e inicialización del proyecto **`aero-bff`** como **Backend for Frontend (BFF)** y API Gateway para **Aero Agripac** basado en **Symfony 7.4** y **PHP 8.2**.

### Características y Principios Arquitectónicos
1. **Cascarón Puro (Thin Shell):**
   - No contiene persistencia ni ORM (sin Doctrine DBAL/ORM).
   - Sin dependencias de base de datos directas ni migraciones.
2. **Cero Lógica de Negocio:**
   - La lógica de negocio está centralizada en el Core Backend (`SAeroPlanificacion`), modelada a partir de `aero-back` (Laravel).
3. **Propagación Segura:**
   - Servicio `SpringBackendClient` encargado de inyectar `X-Internal-Secret`, `X-User-Id` y `X-User-Role` en cada petición dirigida al cluster interno de Spring Boot.
4. **Endpoint de Salud Unificado:**
   - `GET /api/health` evalúa la operatividad del gateway Symfony y realiza un sondeo activo a `GET /api/internal/health` en Spring Boot.
