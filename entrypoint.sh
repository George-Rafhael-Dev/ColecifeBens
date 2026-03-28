#!/bin/sh

# para se der erro
set -e

echo "Iniciando container..."

# cria .env se não existir
if [ ! -f .env ]; then
  echo "Criando .env..."
  cp .env.example .env
fi

# instala dependências se não existir vendor
if [ ! -d vendor ]; then
  echo "Instalando dependências (composer install)..."
  composer install
fi

# gera key só se não tiver
if ! grep -q "^APP_KEY=base64:" .env; then
  echo "Gerando APP_KEY..."
  php artisan key:generate
fi

# inicia servidor
echo "Subindo Laravel..."
exec php artisan serve --host=0.0.0.0 --port=8000