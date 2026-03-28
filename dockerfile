FROM php:8.2-cli

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl

# Instala composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define diretório
WORKDIR /app

# Copia projeto
COPY . .

# Instala dependências PHP
RUN composer install