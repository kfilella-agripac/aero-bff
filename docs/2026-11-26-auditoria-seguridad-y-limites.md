# Auditoría de seguridad, autenticación Bearer y límites de subida (aero-bff)

**Fecha:** 2026-11-26  
**Rama:** `fix/seguridad-y-limites`  
**Alcance:** Autenticación global de tokens Bearer, CORS configurable, proxies confiables y límites de subida PHP (30 MB / 50 MB).

## 1. Hallazgos de auditoría identificados

1. **Rutas `/api/*` sin autenticación obligatoria:** `security.yaml` y los suscriptores previos no exigían `Bearer` token de forma global en el BFF; solo las rutas de fitosanitario validaban rol.
2. **CORS abierto (`nelmio_cors.yaml`):** `allow_origin` estaba fijo en `['*']` ignorando la variable de entorno `CORS_ALLOW_ORIGIN`.
3. **Sin configuración de `trusted_proxies` (`framework.yaml`):** detrás de Dokploy/Traefik HTTPS, Symfony necesitaba confiar en cabeceras `X-Forwarded-*`.
4. **Límites de subida PHP por defecto (`Dockerfile`):** `upload_max_filesize=2M` y `post_max_size=8M` bloqueaban adjuntos antes de llegar a Spring Boot.
5. **Pendiente de mediano plazo:** reemplazar el servidor integrado `php -S` por FrankenPHP o PHP-FPM + Nginx antes de cargas concurrentes en producción.

## 2. Remediaciones implementadas

- **`src/EventSubscriber/BearerAuthSubscriber.php`:** valida `Authorization: Bearer <token>` mediante `TokenRoleResolver` (`/auth/me` con caché de 60s) en todas las rutas `/api/*`, exceptuando `OPTIONS`, `/api/health`, `/api/login` y `/api/files/*`. Responde HTTP `401` si falta o es inválido.
- **`config/packages/nelmio_cors.yaml`:** `allow_origin` enlazado a `['%env(CORS_ALLOW_ORIGIN)%']`.
- **`config/packages/framework.yaml`:** habilitado `trusted_proxies` (`SYMFONY_TRUSTED_PROXIES`) y cabeceras `x-forwarded-*`.
- **`Dockerfile`:** agregado `99-uploads.ini` con `upload_max_filesize = 30M`, `post_max_size = 55M` y `memory_limit = 256M`.
- **`tests/EventSubscriber/BearerAuthSubscriberTest.php`:** prueba automatizada para rechazo `401` sin token o token inválido, acceso con token válido y rutas públicas.
