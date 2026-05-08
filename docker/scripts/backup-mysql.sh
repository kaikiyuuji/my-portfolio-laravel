#!/usr/bin/env bash
# Backup diário do MySQL → DO Spaces (mesma região, bucket privado).
# Executar via cron no host do droplet (não dentro do container app).
#
# Cron sugerido (host):
#   0 2 * * *  /opt/portfolio/docker/scripts/backup-mysql.sh >> /var/log/portfolio-backup.log 2>&1
#
# Variáveis lidas de /opt/portfolio/.env (mesmo arquivo do docker-compose):
#   DB_DATABASE
#   DB_ROOT_PASSWORD (ou DB_PASSWORD)
#   AWS_ACCESS_KEY_ID
#   AWS_SECRET_ACCESS_KEY
#   AWS_DEFAULT_REGION
#   AWS_BUCKET                  # ex: portfolio-backups
#   AWS_ENDPOINT                # ex: https://nyc3.digitaloceanspaces.com
#
# Retenção: 14 dias rolling (configurada via lifecycle policy do bucket).
# Caso o bucket não tenha lifecycle, descomente o bloco "Retenção local".

set -euo pipefail

PROJECT_DIR="${PROJECT_DIR:-/opt/portfolio}"
COMPOSE_FILE="$PROJECT_DIR/docker-compose.yml"
ENV_FILE="$PROJECT_DIR/.env"
LOCAL_BACKUP_DIR="${LOCAL_BACKUP_DIR:-/var/backups/portfolio}"
RETENTION_DAYS="${RETENTION_DAYS:-14}"

if [[ ! -f "$ENV_FILE" ]]; then
    echo "[backup] .env não encontrado em $ENV_FILE" >&2
    exit 1
fi

# shellcheck disable=SC1090
set -a
source "$ENV_FILE"
set +a

ROOT_PWD="${DB_ROOT_PASSWORD:-${DB_PASSWORD:-}}"
if [[ -z "$ROOT_PWD" ]]; then
    echo "[backup] DB_ROOT_PASSWORD/DB_PASSWORD ausente" >&2
    exit 1
fi

mkdir -p "$LOCAL_BACKUP_DIR"

TS="$(date -u +%Y%m%d-%H%M%SZ)"
DUMP_FILE="$LOCAL_BACKUP_DIR/portfolio-$TS.sql.gz"

echo "[backup] dump $DB_DATABASE → $DUMP_FILE"
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" exec -T mysql \
    sh -c "exec mysqldump --single-transaction --quick --lock-tables=false \
        -uroot -p\"$ROOT_PWD\" \"$DB_DATABASE\"" \
    | gzip -9 > "$DUMP_FILE"

# Upload para Spaces via aws-cli (configurado com endpoint custom).
if command -v aws >/dev/null 2>&1 && [[ -n "${AWS_BUCKET:-}" ]]; then
    REMOTE="s3://$AWS_BUCKET/mysql/portfolio-$TS.sql.gz"
    echo "[backup] upload → $REMOTE"
    AWS_ACCESS_KEY_ID="$AWS_ACCESS_KEY_ID" \
    AWS_SECRET_ACCESS_KEY="$AWS_SECRET_ACCESS_KEY" \
    aws --endpoint-url "${AWS_ENDPOINT:?endpoint ausente}" \
        s3 cp "$DUMP_FILE" "$REMOTE" --acl private
else
    echo "[backup] aws-cli ou AWS_BUCKET ausente — mantendo apenas backup local"
fi

# Retenção local: remove dumps com mais de N dias.
find "$LOCAL_BACKUP_DIR" -type f -name 'portfolio-*.sql.gz' -mtime +"$RETENTION_DAYS" -delete

echo "[backup] OK $TS"
