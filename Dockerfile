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

# On container start: generate app key if missing, run migrations, link
# storage, cache config + routes, then boot Laravel's built-in server on $PORT.
CMD sh -c "\
    php artisan key:generate --force && \
    php artisan migrate --force && \
    php artisan storage:link || true && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host 0.0.0.0 --port \"\${PORT:-8000}\""