FROM composer:latest as builder
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip
WORKDIR /app
COPY backend/ .
RUN composer install --no-dev --optimize-autoloader --no-scripts

FROM builder as dev-builder
WORKDIR /app
RUN composer install --dev --optimize-autoloader

FROM php:8.2-fpm as prod
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html
EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000

FROM prod as dev
COPY --from=dev-builder /app .
RUN apt update && apt install -y sqlite3
RUN pecl install xdebug && docker-php-ext-enable xdebug
CMD php artisan serve --host=0.0.0.0 --port=8000
