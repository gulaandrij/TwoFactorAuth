FROM php:7.2-apache

RUN apt-get update
RUN apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev curl bzip2 zip unzip libmagickwand-dev

RUN docker-php-ext-configure gd \
        --enable-gd-native-ttf \
        --with-freetype-dir=/usr/include/freetype2 \
        --with-png-dir=/usr/include \
        --with-jpeg-dir=/usr/include

RUN docker-php-ext-install mysqli pdo pdo_mysql gd
RUN pecl install imagick
RUN docker-php-ext-enable imagick
RUN a2enmod rewrite