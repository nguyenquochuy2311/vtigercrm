FROM php:7.4-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libxml2-dev \
        libicu-dev \
        libc-client-dev \
        libkrb5-dev \
        libcurl4-openssl-dev \
        libonig-dev \
        unzip \
        ca-certificates \
        && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        mysqli \
        pdo_mysql \
        zip \
        xml \
        intl \
        imap \
        mbstring \
        curl \
        soap \
        opcache

RUN a2enmod rewrite headers expires

COPY php.ini /usr/local/etc/php/conf.d/zz-vtiger.ini
COPY apache-vtiger.conf /etc/apache2/conf-available/vtiger.conf
RUN a2enconf vtiger

WORKDIR /var/www/html
