FROM php:8.4-fpm-alpine

# Установка системных зависимостей (добавлены библиотеки для GD)
RUN apk add --no-cache \
    bash \
    curl \
    libpq-dev \
    oniguruma-dev \
    libzip-dev \
    libxml2-dev \
    git \
    unzip \
    zip \
    freetype-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    $PHPIZE_DEPS # Добавлено для сборки phpredis

# Установка PHP расширений (включая pdo_pgsql для PostgreSQL) (добавлено конфигурирование и установка gd)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath zip ctype gd

# Установка расширения phpredis через pecl
RUN pecl install redis && docker-php-ext-enable redis

# Очистка после сборки phpredis
RUN apk del $PHPIZE_DEPS

# Установка Composer из официального образа
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка рабочей директории
WORKDIR /var/www

# Создание пользователя с тем же UID/GID, что и у хоста
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} laravel && \
    adduser -D -u ${UID} -G laravel laravel

# Права для корректной работы Laravel на Linux серверах при деплое
RUN chown -R laravel:laravel /var/www

USER laravel

EXPOSE 9000
CMD ["php-fpm"]