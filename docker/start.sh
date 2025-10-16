#!/bin/sh
set -e

# Default port if not provided by Render
: ${PORT:=8000}

# Replace placeholder port in nginx config
if [ -f /etc/nginx/nginx.conf ]; then
  sed -i "s/__PORT__/${PORT}/g" /etc/nginx/nginx.conf || true
fi

# Ensure permissions
mkdir -p /app/var /app/public/uploads
chown -R www-data:www-data /app/var || true
chmod -R 755 /app/public/uploads || true

# (Optional) warm up cache or run migrations here if you want
# php bin/console doctrine:migrations:migrate --no-interaction || true

# Start php-fpm in background
php-fpm &

# Start nginx in foreground (Render expects the container to keep running)
nginx -g 'daemon off;'

