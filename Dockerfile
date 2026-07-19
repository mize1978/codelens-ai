# ── Stage 1: Build frontend assets ──────────────────────────────────
FROM node:20-slim AS frontend
WORKDIR /app
COPY package.json ./
RUN npm install
COPY . .
RUN npm run build

# ── Stage 2: PHP runtime ─────────────────────────────────────────────
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev zip \
    && docker-php-ext-install pdo pdo_pgsql zip pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
COPY --from=frontend /app/public/build /app/public/build

RUN composer install --optimize-autoloader --no-dev
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
