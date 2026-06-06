FROM php:7.4-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
        libldap2-dev \
        libicu-dev \
        libonig-dev \
        libc-client-dev \
        libkrb5-dev \
        libxslt1-dev \
        unzip \
        cron \
        ca-certificates \
        curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        gd \
        iconv \
        imap \
        intl \
        ldap \
        mbstring \
        mysqli \
        opcache \
        pdo_mysql \
        soap \
        xml \
        xmlrpc \
        xsl \
        zip \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

COPY vtiger.ini /usr/local/etc/php/conf.d/zz-vtiger.ini
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

EXPOSE 80

# Fix bind-mount ownership + remap www-data UID/GID on every start, then run CMD
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
