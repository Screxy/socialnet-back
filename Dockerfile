FROM php:8.3-fpm as environment

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
        curl \
        wget \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libonig-dev \
        libzip-dev \
    && docker-php-ext-install -j$(nproc) iconv mbstring mysqli pdo_mysql zip \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

FROM environment as builder

COPY . .

RUN composer install -o --no-interaction

FROM environment as final

COPY --from=builder /var/www/vendor /var/www/vendor
COPY --from=builder /var/www/composer.lock /var/www/composer.lock
ADD config/php/php.ini /usr/local/etc/php

CMD ["php-fpm"]
