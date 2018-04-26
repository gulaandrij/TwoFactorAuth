FROM php:7.2-apache

RUN apt-get update
RUN apt-get install -y --no-install-recommends \
    apt-utils \
    wget \
    git \
    bzip2 \
    build-essential \
    zip \
    unzip
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite