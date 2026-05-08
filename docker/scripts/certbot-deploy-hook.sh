#!/usr/bin/env bash
# Deploy hook para Certbot — reload nginx no container após renovação.
# Instalar como /etc/letsencrypt/renewal-hooks/deploy/portfolio-nginx.sh
# (chmod +x). Certbot executa após cada renovação bem-sucedida.
set -euo pipefail

PROJECT_DIR="${PROJECT_DIR:-/home/deploy/portfolio}"

cd "$PROJECT_DIR"
docker compose --env-file .env exec -T nginx nginx -s reload
echo "[certbot-hook] nginx reload OK"
