# Backend (Laravel) - PHP-FPM 8.2 com Composer e extensões necessárias
FROM php:8.2-fpm

# Instala dependências do sistema e extensões do PHP
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libpng-dev \
        libonig-dev \
        libzip-dev \
        libxml2-dev \
        zip \
        libpq-dev \
        default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# Instala o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Otimizações do PHP-FPM para dev (pode ajustar para prod)
COPY ./docker-php.ini /usr/local/etc/php/conf.d/docker-php.ini

# Permissões (ajuste UID/GID se necessário)
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data \
    && chown -R www-data:www-data /var/www

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
