#!/bin/sh
# Bootstrap do container "app".
#
# O que faz e por quê:
#   1. Espera o MySQL aceitar conexões antes de deixar o Apache subir.
#      O docker-compose já usa `depends_on: condition: service_healthy`,
#      o que cobre o caso normal de `docker compose up`. Este wait aqui é
#      uma segunda camada de segurança para quando o container "app" é
#      reiniciado/recriado isoladamente (ex.: `docker compose up -d app`,
#      ou `docker restart`) sem passar pelo scheduler do compose — nesses
#      casos o depends_on não é reavaliado.
#   2. Garante que os diretórios que o CodeIgniter precisa escrever
#      (cache, logs, sessões em arquivo) existem e têm permissão certa.
#      Isso é idempotente e barato — não é um "rebuild de cache" pesado,
#      só evita erros de permissão silenciosos na primeira request.
#   3. Não faz nenhum aquecimento de cache de framework: o CodeIgniter 3
#      não tem um equivalente a `config:cache`/`route:cache` do Laravel
#      (rotas e config são simples arrays PHP, já compilados pelo OPcache
#      na primeira request). Não há nada custoso para "esquentar" aqui.
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
MAX_WAIT=30

if [ -n "$DB_HOST" ]; then
	echo "[start-app] aguardando MySQL em ${DB_HOST}:${DB_PORT}..."
	waited=0
	while ! php -r "exit(@fsockopen('${DB_HOST}', ${DB_PORT}, \$e, \$s, 1) ? 0 : 1);"; do
		waited=$((waited + 1))
		if [ "$waited" -ge "$MAX_WAIT" ]; then
			echo "[start-app] AVISO: MySQL não respondeu após ${MAX_WAIT}s, subindo o Apache assim mesmo." >&2
			break
		fi
		sleep 1
	done
	echo "[start-app] MySQL disponível (ou timeout de espera atingido)."
fi

SESSION_DIR="${SESSION_SAVE_PATH:-/var/lib/ci-sessions}"
mkdir -p /var/www/html/application/cache /var/www/html/application/logs "$SESSION_DIR"
chown -R www-data:www-data /var/www/html/application/cache /var/www/html/application/logs "$SESSION_DIR" 2>/dev/null || true

echo "[start-app] iniciando Apache."
exec apache2-foreground
