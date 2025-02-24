FROM php:8.1-apache

# RUN chown -R www-data:www-data /var/www/html/var

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN chown -R www-data:www-data /var/www/html/var

EXPOSE 80

CMD ["apache2-foreground"]