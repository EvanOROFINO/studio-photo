# =============================================================================
# Dockerfile — Studio Photo (Symfony 7.4 / PHP 8.2)
# -----------------------------------------------------------------------------
# Image utilisée pour le déploiement Railway / Fly.io / Render.
# Construit en mode production avec composer install --no-dev + cache warmup.
# =============================================================================

FROM php:8.2-apache

# 1. System libs needed by PHP extensions (gd, intl, mysql, zip)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    libicu-dev libonig-dev libxml2-dev libzip-dev \
    zip unzip git \
    && rm -rf /var/lib/apt/lists/*

# 2. PHP extensions
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j$(nproc) \
        gd intl pdo pdo_mysql opcache zip

# 3. Apache: enable mod_rewrite, point document root to public/
RUN a2enmod rewrite headers
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!\${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!\${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 5. App source
WORKDIR /var/www/html
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction

COPY . .

# 6. Finalise install + permissions
RUN composer dump-autoload --optimize --classmap-authoritative \
    && mkdir -p var/cache var/log var/client-galleries public/uploads/photos public/uploads/articles public/uploads/avatars public/uploads/before-after public/uploads/products public/media/cache \
    && chown -R www-data:www-data var public/uploads public/media \
    && chmod -R 775 var public/uploads public/media

# 7. Apache listens on Railway's $PORT (defaults to 8080)
ENV PORT=8080
RUN sed -ri "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf \
    && sed -ri "s/:80>/:\${PORT}>/" /etc/apache2/sites-available/000-default.conf

# 8. Entrypoint: run migrations on boot then start Apache
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
