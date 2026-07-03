FROM php:8.4-apache

# Install system dependencies required for your PHP extensions
RUN apt-get update && apt-get install -y \
    cron \
    libsqlite3-dev \
    zip \
    unzip \
    git \
    libicu-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    --no-install-recommends \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure the GD extension to support common image formats
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install standard core PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    intl \
    mbstring \
    curl \
    xml \
    zip \
    fileinfo \
    gd

# Install Imagick via PECL (since it isn't bundled with PHP core)
RUN pecl install imagick && docker-php-ext-enable imagick

# Enable Apache mod_rewrite for CodeIgniter's routing
RUN a2enmod rewrite

# Update Apache document root to point to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Cache the git repository's template writable folder safely in /tmp
COPY writable /tmp/writable_backup

WORKDIR /var/www/html

EXPOSE 80
