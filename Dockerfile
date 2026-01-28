# Use imagem PHP 8.2 com Apache
FROM php:8.2-apache

# Atualiza sistema e instala dependências
RUN apt-get update && apt-get install -y \
    zip unzip git default-mysql-client libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean

# Habilita módulos do Apache
RUN a2enmod rewrite headers

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia composer.json e composer.lock primeiro (para cache de layers)
COPY composer.json composer.lock /var/www/html/

# Define o diretório de trabalho
WORKDIR /var/www/html

# Instala dependências (vendor dentro da imagem)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copia todo o restante do código
COPY . /var/www/html

# Copia e configura Xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Permissões corretas
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Porta exposta
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
