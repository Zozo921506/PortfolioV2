FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    curl \
    git \
    vim \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd intl zip \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY apache.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install

RUN composer dump-autoload && \
cat /var/www/html/vendor/autoload_runtime.php

RUN chown -R www-data:www-data /var/www/html

RUN chmod -R 775 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]