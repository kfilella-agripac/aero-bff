# Aero-BFF (Backend-For-Frontend)

API Gateway y capa de orquestación para **Aero Agripac**, desarrollado con **Symfony 7.4** y **PHP 8.4** (parche de seguridad 8.4.27).

Actúa como punto de entrada seguro para el panel web administrativo (Vue 3) y la aplicación móvil de campo (Flutter), comunicándose de forma interna y segura con el Core transaccional en **Spring Boot 3.3**.

---

## 🚀 Requisitos Previos

* **Docker & Docker Compose** (recomendado v24+)
* **PHP 8.4+** y **Composer 2** (opcional, solo si deseas ejecutarlo de forma nativa sin Docker)

---

## ⚡ Inicio Rápido con Docker (Recomendado)

El repositorio incluye su propia configuración autónoma de Docker Compose:

1. **Configurar variables de entorno (opcional si usas los defaults):**
   ```bash
   cp .env.example .env.local
   ```

2. **Levantar el contenedor del BFF:**
   ```bash
   docker compose up --build
   ```

3. **¡Listo!**
   * **API Gateway:** `http://localhost:8000` (o el configurado en `BFF_PORT`)
   * **Health Check Integrado:** `http://localhost:8000/api/health`

Para detener el servicio:
```bash
docker compose down
```

---

## 🔗 Conexión con Spring Boot Core

El BFF necesita comunicarse con el backend en Spring Boot (`SAeroPlanificacion`). Puedes configurar la URL de Spring Boot en tu `.env.local`:

* **Si Spring Boot corre en tu máquina (puerto 8080):**
  ```env
  SPRING_BACKEND_URL=http://host.docker.internal:8080/api/internal
  SPRING_INTERNAL_SECRET=saero-internal-secret-dev-2026
  ```
* **Si Spring Boot corre en un servidor remoto o red local:**
  ```env
  SPRING_BACKEND_URL=http://192.168.1.50:8080/api/internal
  SPRING_INTERNAL_SECRET=saero-internal-secret-dev-2026
  ```

---

## 💻 Desarrollo Local sin Docker

Si prefieres ejecutar el BFF con las herramientas nativas de tu sistema:

```bash
# 1. Instalar dependencias
composer install

# 2. Levantar servidor de desarrollo
php -S 0.0.0.0:8000 -t public
# O utilizando el CLI de Symfony:
symfony server:start --port=8000
```

---

## 🧪 Pruebas Automatizadas

Para ejecutar la suite de pruebas unitarias y de integración:

```bash
php bin/phpunit
# O dentro del contenedor Docker:
docker compose exec bff php bin/phpunit
```
