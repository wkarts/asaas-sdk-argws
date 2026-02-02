FROM php:8.1-cli

RUN apt-get update \
    && apt-get install -y git unzip libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY composer.json composer.lock* ./
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

RUN composer install --no-dev --no-interaction --prefer-dist --no-progress

COPY . /app

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
