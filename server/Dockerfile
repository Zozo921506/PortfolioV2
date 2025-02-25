FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev \
    libzip-dev default-mysql-client

RUN docker-php-ext-install pdo pdo_mysql zip gd

RUN a2enmod rewrite

WORKDIR /var/www

COPY . /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-scripts

EXPOSE 80

# RUN sed -i '/var/www/html/public' \
#     /etc/apache2/sites-available/000-default.conf

# RUN chown -R www-data:www-data /var/www/html/var

CMD "apache2-foreground"
