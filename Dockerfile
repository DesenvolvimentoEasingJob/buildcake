# Use imagem PHP 8.2 com Apache
# Build context = pasta back/ (projeto independente; deps via composer)
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    zip unzip git default-mysql-client libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean

RUN a2enmod rewrite headers

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . /var/www/html

COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
