FROM php:8.1-cli

RUN apt-get update \
    && apt-get install -y git unzip libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Este Dockerfile foi ajustado para funcionar com build context apontando para a pasta playground
# OU para a raiz do repositório. Se o build context for a raiz, o compose abaixo já aponta para ./playground.

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

WORKDIR /app/playground

# Copia só o que precisa para instalar dependências (melhora cache do Docker)
COPY composer.json composer.lock* ./

RUN composer install --no-dev --no-interaction --prefer-dist --no-progress

# Copia o restante do playground (public/, src/, views/, etc.)
COPY . ./

# Usa router file para o Slim tratar as rotas corretamente
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/index.php"]
