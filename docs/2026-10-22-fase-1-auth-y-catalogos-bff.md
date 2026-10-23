# Fase 1: Controladores de Autenticación y Catálogos en BFF Symfony

**Fecha:** 2026-10-22  
**Módulo:** `aero-bff` (Symfony 7.4 / PHP 8.4.27)  
**Estado:** Implementado y Verificado  

---

## 1. Resumen
Se implementaron los controladores de perímetro en el Gateway Symfony para autenticación y consulta de catálogos maestros, delegando todas las operaciones hacia el Core Backend en Spring Boot:

- **Actualización de Runtime PHP:**
  - Actualizado el contenedor Docker a `php:8.4-cli` (`PHP 8.4.27`), aplicando los parches de seguridad críticos recientes.
- **Propagación de Contexto de Autenticación:**
  - Se extendió `SpringBackendClient::forward()` para reenviar de forma transparente las cabeceras `Authorization` y `X-Api-Token` al backend de Spring Boot.

---

## 2. Endpoints Expuestos

### Autenticación (`/api/**`)
- `POST /api/login`: Reenvía las credenciales a `/api/internal/auth/login` y retorna el token y perfil del usuario.
- `GET /api/me`: Reenvía el token a `/api/internal/auth/me`.
- `POST /api/logout`: Reenvía la revocación del token a `/api/internal/auth/logout`.

### Catálogos Maestros (`/api/**`)
- `GET /api/pistas`: Consulta listado de pistas.
- `GET /api/clientes`: Consulta catálogo de clientes.
- `GET /api/zonas`: Consulta zonas geográficas.
- `GET /api/fincas`: Consulta fincas agrícolas.
- `GET /api/sectors` y `GET /api/sectores`: Consulta sectores.
- `GET /api/finca-sectors`: Consulta sectores de fincas.
- `GET /api/aeronaves`: Consulta flota de aeronaves.
- `GET /api/pilotos`: Consulta catálogo de pilotos.
- `GET /api/mecanicos`: Consulta catálogo de mecánicos.
- `GET /api/settings`: Consulta parámetros del sistema.

---

## 3. Pruebas
Se crearon pruebas funcionales en `tests/Controller/AuthControllerTest.php` validando el enrutamiento y delegación hacia Spring Boot.
