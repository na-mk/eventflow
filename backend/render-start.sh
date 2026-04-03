#!/usr/bin/env sh
set -eu

export APP_ENV="${APP_ENV:-prod}"
export APP_DEBUG="${APP_DEBUG:-0}"

mkdir -p var/jwt

if [ -n "${JWT_PRIVATE_KEY_B64:-}" ]; then
  printf '%s' "$JWT_PRIVATE_KEY_B64" | base64 -d > var/jwt/private.pem
fi

if [ -n "${JWT_PUBLIC_KEY_B64:-}" ]; then
  printf '%s' "$JWT_PUBLIC_KEY_B64" | base64 -d > var/jwt/public.pem
fi

if [ ! -f "${JWT_SECRET_KEY:-/app/var/jwt/private.pem}" ] || [ ! -f "${JWT_PUBLIC_KEY:-/app/var/jwt/public.pem}" ]; then
  echo "JWT key files are missing. Set JWT_PRIVATE_KEY_B64 and JWT_PUBLIC_KEY_B64 on Render." >&2
  exit 1
fi

php bin/console doctrine:migrations:migrate --no-interaction
php -S 0.0.0.0:"${PORT:-10000}" -t public
