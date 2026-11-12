FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json ./
RUN npm install

COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    libpq-dev \
    unzip \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

COPY --from=assets /app/public/build ./public/build

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD ["sh", "-c", "php artisan migrate --force && if [ \"${RUN_DB_SEED:-}\" = \"true\" ] || php -r 'require \"vendor/autoload.php\"; (require \"bootstrap/app.php\")->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); try { exit(\\App\\Models\\Step::count() === 0 ? 0 : 1); } catch (\\Throwable) { exit(0); }'; then php artisan db:seed --force; fi && PHP_CLI_SERVER_WORKERS=${PHP_CLI_SERVER_WORKERS:-4} php artisan serve --no-reload --host=0.0.0.0 --port=${PORT:-10000}"]