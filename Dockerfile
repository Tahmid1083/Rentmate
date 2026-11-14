# Render-compatible PHP image for the Rentmate Laravel app.
# Uses the official php:8.2-cli image (small, stable, no extra web server needed —
# Laravel's `artisan serve` command is the dev server built into PHP itself).

FROM php:8.2-cli

# Install OS packages + PHP extensions required by Laravel 11.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git curl libpng-dev libjpeg-dev libfreetype6-dev \
        libonig-dev libxml2-dev libzip-dev zip unzip libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy Composer binary from the official Composer image.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy the project files (respects .dockerignore — vendor/ etc. are excluded).
COPY . .

# Install PHP dependencies.
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Make sure storage / bootstrap are writable by the PHP process.
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# Render injects a $PORT env var (typically 10000). Default to 8000 for local.
ENV PORT=10000
EXPOSE 10000

# Require a persistent key from the hosting environment; never rotate it on
# container restarts because Laravel uses it to encrypt sessions and cookies.
CMD ["sh", "-c", "if [ -z \"${APP_KEY:-}\" ]; then echo 'APP_KEY must be set in the Render environment.' >&2; exit 1; fi; php artisan migrate --force && (php artisan storage:link || true) && php artisan config:cache && php artisan route:cache && php artisan serve --host 0.0.0.0 --port \"${PORT:-8000}\""]