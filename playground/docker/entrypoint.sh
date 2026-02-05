#!/usr/bin/env sh
set -eu

DOCROOT="/app/playground/public"

if [ ! -d "$DOCROOT" ]; then
  echo "[entrypoint] Docroot $DOCROOT não existe. Tentando localizar..." >&2

  if [ -d "/app/public" ]; then
    mkdir -p /app/playground
    ln -sf /app/public "$DOCROOT"
    echo "[entrypoint] Usando /app/public como docroot via symlink." >&2
  else
    echo "[entrypoint] ERRO: docroot não encontrado. Conteúdo em /app/playground:" >&2
    ls -lah /app/playground || true
    exit 1
  fi
fi

exec php -S 0.0.0.0:8080 -t "$DOCROOT"
