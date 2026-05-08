#!/usr/bin/env bash
# Entrypoint do container PHP-FPM em produção.
# Roda APÓS a montagem dos volumes — necessário para storage:link sobreviver
# (ver Hurdle 1 do PLANEJAMENTO.md).
set -euo pipefail

cd /var/www/html

# Garante diretórios de runtime do storage.
mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

# Symlink public/storage → storage/app/public (idempotente).
php artisan storage:link --force || true

# Cache de config/route/view só após o .env de produção estar montado
# (ver Hurdle 12). Não cachear em build.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Executa o comando recebido (CMD do Dockerfile = php-fpm).
exec "$@"
