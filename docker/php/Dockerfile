# docker/php/Dockerfile
FROM php:8.2-fpm

# 시스템 패키지: unzip, git, libzip-dev 
RUN apt-get update \
 && apt-get install -y git unzip libzip-dev \
 && docker-php-ext-install mysqli zip pdo pdo_mysql \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html