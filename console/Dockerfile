FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libonig-dev libzip-dev libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install pdo_pgsql mbstring opcache bcmath \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# 先创建必要的目录
RUN mkdir -p storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/framework/testing storage/logs storage/app/public \
        bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

COPY . .

# 安装 composer 依赖，跳过 artisan 优化脚本（需要 bootstrap/cache 可写）
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --ignore-platform-req=ext-zip

# 补齐 Laravel 标准配置：仓库已提供的（app.php / database.php）保留，
# 其余缺失的（cache/session/view/logging/...）用框架默认填充，保证 artisan 可运行
RUN cp -n vendor/laravel/framework/config/*.php config/ 2>/dev/null || true

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
