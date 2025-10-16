#!/bin/sh
set -e

# Default port if not provided by Render
: ${PORT:=8000}

# Replace placeholder port in nginx config
if [ -f /etc/nginx/nginx.conf ]; then
  sed -i "s/__PORT__/${PORT}/g" /etc/nginx/nginx.conf || true
fi

# Ensure php-fpm listens on 127.0.0.1:9000 (Render nginx config expects this)
PHP_FPM_CONF="/usr/local/etc/php-fpm.d/www.conf"
if [ -f "$PHP_FPM_CONF" ]; then
  # set listen to 127.0.0.1:9000 (works if original has a listen directive)
  sed -i "s/^listen\s*=.*$/listen = 127.0.0.1:9000/" "$PHP_FPM_CONF" || true
fi

# Ensure permissions
mkdir -p /app/var /app/public/uploads
chown -R www-data:www-data /app/var || true
chmod -R 755 /app/public/uploads || true

# Optional: run migrations if RUN_MIGRATIONS=true
# WARNING: running migrations automatically in production can be risky.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "RUN_MIGRATIONS is true — running doctrine migrations"
  # Ensure environment variables are set for console execution
  export APP_ENV=${APP_ENV:-prod}
  export APP_DEBUG=${APP_DEBUG:-0}
  if [ -f /app/bin/console ]; then
    # attempt migrations (ignore non-zero exit to avoid container stop)
    php /app/bin/console doctrine:migrations:migrate --no-interaction || true
  else
    echo "Console not found, skipping migrations"
  fi
fi

# Start php-fpm in background
php-fpm &

# Start nginx in foreground (Render expects the container to keep running)
nginx -g 'daemon off;'

