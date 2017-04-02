FROM php:5.6

RUN apt-get update \
    && apt-get install -y libmemcached-dev zlib1g-dev libpq-dev \
    && yes '' | pecl install memcache \
    && docker-php-ext-enable memcache \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

COPY . /nota

WORKDIR /nota/www

COPY ./protected/config/timezone.ini /usr/local/etc/php/conf.d/timezone.ini
