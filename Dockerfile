# Aero-BFF: Gateway API (Symfony 7.4 / PHP 8.4 CLI - patched 8.4.28)
FROM php:8.4.28-cli

# Dependencias mínimas del sistema para API Gateway
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        curl \
    && docker-php-ext-install -j"$(nproc)" \
        mbstring \
        zip \
        intl \
        bcmath \
        pcntl \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Propagar variables de entorno al servidor de desarrollo de PHP
RUN echo 'variables_order = "EGPCS"' > /usr/local/etc/php/conf.d/99-variables-order.ini

# Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts

COPY . .
RUN composer dump-autoload --optimize \
    && mkdir -p var/cache var/log

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
