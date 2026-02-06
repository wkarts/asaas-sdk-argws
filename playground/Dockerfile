FROM php:8.1-cli

RUN apt-get update \
    && apt-get install -y git unzip libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY composer.json composer.lock* ./
COPY playground/composer.json playground/composer.lock* ./playground/

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

WORKDIR /app/playground
RUN test -f /app/playground/composer.json
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress

WORKDIR /app
COPY . /app

# falha rápido se a pasta pública não estiver presente (evita container subindo e caindo com "Directory public does not exist")
RUN test -d /app/playground/public

WORKDIR /app/playground

# usa caminhos absolutos para não depender do CWD (e evitar "Directory public does not exist")
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app/playground/public", "/app/playground/public/index.php"]
