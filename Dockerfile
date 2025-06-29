# Dockerfile (multi-stage build: development and production)

# ------------ Base Stage ------------
FROM php:8.2-fpm-alpine AS base

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash curl git mysql-client icu \
    freetype libjpeg-turbo libpng libwebp zip unzip libzip \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS icu-dev zlib-dev libzip-dev \
    freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        gd intl zip pdo_mysql bcmath opcache exif \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# ------------ Dev Stage ------------
FROM base AS dev

RUN addgroup -g 1000 laravel \
    && adduser -D -G laravel -u 1000 -s /bin/sh laravel

COPY --chown=laravel:laravel . .

USER laravel

# ------------ Production Stage with Apache ------------
FROM php:8.2-apache AS web

# Copy from base build
COPY --from=base /usr/local/etc/php/conf.d/docker-php-ext-* /usr/local/etc/php/conf.d/
COPY --from=base /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/

# Enable Apache mod_rewrite
RUN apt-get update && apt-get install -y \
    zip unzip git curl gnupg libzip-dev nodejs npm \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set Laravel public directory as Apache root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /var/www/html
WORKDIR /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Optimize Laravel
RUN composer install --no-dev --optimize-autoloader \
    && npm install && npm run build \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]

