# ==========================================
# Stage 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS node-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY resources/ ./resources/
COPY vite.config.js tailwind.config.js* postcss.config.js* ./
RUN npm run build

# ==========================================
# Stage 2: Build Composer Dependencies
# ==========================================
FROM composer:2 AS composer-builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# ==========================================
# Stage 3: Base PHP 8.3 FPM Alpine Runtime
# ==========================================
FROM php:8.3-fpm-alpine AS base

# Install system runtime dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-libs \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    libpq \
    oniguruma \
    fcgi \
    netcat-openbsd \
    supervisor

# Install build dependencies and compile PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    icu-dev \
    postgresql-dev \
    oniguruma-dev \
    linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

# Create non-root user
RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser -h /var/www -s /bin/bash appuser

WORKDIR /var/www

# Copy PHP and Supervisor configs
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-enterprise-php.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

# Copy entrypoint and healthcheck scripts
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/healthcheck.sh /usr/local/bin/healthcheck.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/healthcheck.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# ==========================================
# Stage 4: Production Image
# ==========================================
FROM base AS production

# Copy vendor from composer-builder
COPY --chown=appuser:www-data --from=composer-builder /app/vendor /var/www/vendor

# Copy application source code
COPY --chown=appuser:www-data . /var/www

# Copy compiled frontend assets from node-builder
COPY --chown=appuser:www-data --from=node-builder /app/public/build /var/www/public/build

# Set permissions for Laravel writable directories
RUN mkdir -p /var/www/storage/framework/cache/data \
             /var/www/storage/framework/sessions \
             /var/www/storage/framework/views \
             /var/www/storage/logs \
             /var/www/storage/app/public \
             /var/www/storage/app/private/identity_documents \
             /var/www/bootstrap/cache \
    && chown -R appuser:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

USER appuser

EXPOSE 9000

CMD ["php-fpm"]

# ==========================================
# Stage 5: Development Image
# ==========================================
FROM base AS development

# Install Composer CLI in development image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

USER appuser

EXPOSE 9000

CMD ["php-fpm"]
