FROM php:8.4-fpm-alpine

# Install system utilities, Nginx, and PostgreSQL client libraries
RUN apk update && apk add --no-cache \
    nginx \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    bash \
    curl

# Install PHP extensions required for Laravel and PostgreSQL
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        zip \
        gd \
        bcmath \
        opcache

# Configure production OPcache
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.enable_cli=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install production dependencies (skip dev dependencies)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Setup proper permissions and ensure photo directories exist
RUN mkdir -p /var/www/html/storage/app/public/photos /var/www/html/public/photos \
    && cp -n /var/www/html/public/photos/* /var/www/html/storage/app/public/photos/ 2>/dev/null || true \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/public/photos /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/public/photos /var/www/html/bootstrap/cache

# Copy Nginx and entrypoint configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
