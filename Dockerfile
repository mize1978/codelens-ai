FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev zip postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql zip pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN composer install --optimize-autoloader

RUN chmod -R 775 storage bootstrap/cache

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
