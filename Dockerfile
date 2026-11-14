# Production Dockerfile for the Laravel rental management app.
# Works on Fly.io, Render (Docker environment), Railway, Koyeb, etc.

FROM serversideup/php:8.2-fpm-nginx AS base

# Copy the application code
WORKDIR /var/www/html
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Permissions for storage / bootstrap cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Expose the port Render / Fly expects
EXPOSE 8000

# On first run: generate key (if missing), migrate, link storage, cache config.
CMD php artisan key:generate --force \
    && php artisan migrate --force \
    && php artisan storage:link \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan serve --host 0.0.0.0 --port 8000
