FROM php:8.4-fpm-alpine

# Installation of system dependencies (libraries for GD and icu-dev for intl have been added)
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
    icu-dev \
    # --- ADDED FOR PLAYWRIGHT & CHROMIUM ---
    nodejs \
    npm \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ca-certificates \
    ttf-freefont \
    # -------------------------------------------
    $PHPIZE_DEPS # Added to build phpredis

# --- ADDING THE OFFICIAL ALPINE EXTENSION BUILDER ---
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Installation of PHP extensions (including pdo_pgsql for PostgreSQL and intl for Filament) (configuration and installation of gd and the sockets extension have been added)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath zip ctype gd intl fileinfo

# Guaranteed installation and activation of sockets for Alpine
RUN install-php-extensions sockets

# Installing the phpredis extension via pecl
RUN pecl install redis && docker-php-ext-enable redis

# Cleaning up after phpredis build
RUN apk del $PHPIZE_DEPS

# Installing Composer from the official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setting up a working directory
WORKDIR /var/www

# Creating a user with the same UID/GID as the host
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} laravel && \
    adduser -D -u ${UID} -G laravel laravel

# Rights for Laravel to work correctly on Linux servers during deployment
RUN chown -R laravel:laravel /var/www

USER laravel

# --- Instruct Playwright to use the system Chromium from Alpine ---
ENV PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=1
ENV PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH=/usr/bin/chromium-browser

EXPOSE 9000
CMD ["php-fpm"]