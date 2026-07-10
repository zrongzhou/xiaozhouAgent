FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libonig-dev libzip-dev libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install pdo_pgsql mbstring opcache bcmath \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# 补齐 Laravel 标准配置：仓库已提供的（app.php / database.php）保留，
# 其余缺失的（cache/session/view/logging/...）用框架默认填充，保证 artisan 可运行
RUN cp -n vendor/laravel/framework/config/*.php config/ 2>/dev/null || true

# 创建运行时 storage 目录并赋权（artisan serve / 视图编译 / 日志需要）
RUN mkdir -p storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/framework/testing storage/logs storage/app/public \
    && chmod -R 777 storage

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
