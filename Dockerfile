FROM php:7.2-apache

RUN apt-get update
RUN apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev curl bzip2 zip unzip

RUN docker-php-ext-configure gd \
        --enable-gd-native-ttf \
        --with-freetype-dir=/usr/include/freetype2 \
        --with-png-dir=/usr/include \
        --with-jpeg-dir=/usr/include

RUN docker-php-ext-install gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite
WORKDIR /var/www/html