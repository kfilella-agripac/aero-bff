# Proxy de Documentos en BFF (Symfony)

**Fecha:** 2026-11-12

## Resumen de Cambios

Se implementó `DocumentController` en `aero-bff` para exponer los endpoints de consulta y eliminación de documentos adjuntos delegando hacia `SAeroPlanificacion`:
- `GET /api/documents` -> `/api/internal/documents`
- `GET /api/documents/{id}` -> `/api/internal/documents/{id}`
- `DELETE /api/documents/{id}` -> `/api/internal/documents/{id}`

## Verificación
- `make test-bff`: **9 tests, 47 assertions** exitosos.
- Suite Playwright de CRUDs del frontend: confirmada la carga sin errores 404 al abrir el diálogo de edición de clientes (`GET /api/documents?documentable_type=cliente&documentable_id=...`).
