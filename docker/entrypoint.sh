#!/bin/sh
# Railway entrypoint: template $PORT into nginx, ensure writables, boot.
set -e
export PORT="${PORT:-8080}"
# nginx has no env expansion: rewrite the listen line in place
sed -i "s/listen \${PORT};/listen ${PORT};/" /etc/nginx/http.d/default.conf
mkdir -p tmp/cache/models tmp/cache/persistent tmp/cache/views tmp/sessions logs
chown -R www-data:www-data tmp logs 2>/dev/null || true
chmod -R 775 tmp logs 2>/dev/null || true
exec /usr/bin/supervisord -c /etc/supervisord.conf
