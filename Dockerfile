## Builder: install extensions and composer dependencies
FROM php:8.2-cli AS builder

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath gd zip exif

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first for caching
COPY composer.json composer.lock* ./

# Install PHP dependencies for production (no-dev)
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --classmap-authoritative

# Copy app source
COPY . .

# Optimize autoload
RUN composer dump-autoload --optimize --classmap-authoritative


## Final image: Apache + PHP (smaller runtime)
FROM php:8.2-apache AS production

# Install only runtime deps
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libzip-dev \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath gd zip exif || true

# Copy application from builder
COPY --from=builder /app /var/www/html

# Set working directory and document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
WORKDIR /var/www/html

# Ensure Apache serves the Laravel public folder
RUN sed -ri 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Ensure storage and cache are writable by Apache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Enable required Apache modules
RUN a2enmod rewrite headers

# Disable Xdebug if present (no-op if not installed)
RUN if php -v 2>/dev/null | grep -qi xdebug; then phpdismod xdebug || true; fi

# Enable and configure OPcache for production
RUN { \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.interned_strings_buffer=16"; \
    echo "opcache.max_accelerated_files=10000"; \
    echo "opcache.revalidate_freq=0"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.enable=1"; \
    echo "opcache.enable_cli=0"; \
} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Copy entrypoint and make it executable
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose default container port (Railway will override with $PORT)
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
