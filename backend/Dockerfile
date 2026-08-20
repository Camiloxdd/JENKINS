FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libicu-dev \
    && docker-php-ext-install pdo_pgsql intl opcache \
    && pecl install apcu && docker-php-ext-enable apcu \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-scripts || true

COPY . .

RUN mkdir -p config/jwt && \
    openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096 && \
    openssl pkey -in config/jwt/private.pem -pubout -out config/jwt/public.pem

RUN composer dump-autoload --optimize --no-dev && \
    chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
