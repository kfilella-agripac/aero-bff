#!/usr/bin/env bash
#
# Entrypoint del contenedor de desarrollo de Aero-BFF (Symfony 7.4 Gateway).
# Idempotente y libre de dependencias a bases de datos.

set -e

cd /var/www/html

# ---------------------------------------------------------------------------
# 1. Dependencias con Composer
# ---------------------------------------------------------------------------
if [ "${APP_INSTALL_DEPS:-false}" = "true" ] || [ ! -f vendor/autoload.php ]; then
    echo "[aero-bff] Verificando dependencias con Composer..."
    if [ ! -f vendor/autoload.php ]; then
        for attempt in 1 2 3 4 5; do
            echo "[aero-bff] Instalando dependencias (intento ${attempt}/5)..."
            if composer install --no-interaction --prefer-dist; then
                break
            fi
            echo "[aero-bff] composer install falló. Reintentando en 3s..."
            sleep 3
            if [ "$attempt" = "5" ]; then
                echo "[aero-bff] ERROR: no se pudo instalar vendor/ tras 5 intentos." >&2
                exit 1
            fi
        done
    fi
fi

# ---------------------------------------------------------------------------
# 2. Limpieza y calentamiento de caché
# ---------------------------------------------------------------------------
if [ -f bin/console ] && [ -f vendor/autoload.php ]; then
    php bin/console cache:clear --no-warmup || true
    php bin/console cache:warmup || true
fi

# ---------------------------------------------------------------------------
# 3. Ejecutar comando principal
# ---------------------------------------------------------------------------
echo "[aero-bff] Arrancando servidor..."
exec "$@"
