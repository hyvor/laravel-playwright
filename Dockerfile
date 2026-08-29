ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-cli

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo pdo_pgsql zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY . /app
WORKDIR /app

RUN composer install \
    && ln -s /app/vendor /app/vendor/orchestra/testbench-core/laravel/vendor # needed for ArtisanTest
