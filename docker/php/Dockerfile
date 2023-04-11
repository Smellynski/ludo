FROM php:8.1-apache
LABEL maintainer="Ralf Michael"
RUN apt-get update -y && apt-get install -y wget sendmail libpng-dev libonig-dev libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libxml2-dev libzip-dev

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev

RUN docker-php-ext-install mbstring zip pdo pdo_mysql mysqli soap
RUN docker-php-ext-configure gd \
        && docker-php-ext-install -j$(nproc) gd

RUN a2enmod rewrite

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html
ENV COMPOSER_HOME=/var/www/composer_home
ENV REMOTE_USER=''

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf