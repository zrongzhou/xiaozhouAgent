FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libonig-dev libzip-dev libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install pdo_pgsql mbstring opcache \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
