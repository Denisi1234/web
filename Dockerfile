# FastNetStays web (CakePHP) — production image for Railway.
# PHP-FPM + nginx + supervisor. Railway injects $PORT; entrypoint templates it.
FROM php:8.2-fpm-alpine

COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    intl \
    mbstring \
    pdo_pgsql \
    opcache \
    zip \
    redis

RUN apk add --no-cache nginx supervisor curl \
    && mkdir -p /etc/supervisor.d /var/log/supervisor /run/nginx

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99_custom.ini
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
    && mkdir -p tmp/cache/models tmp/cache/persistent tmp/cache/views tmp/sessions logs \
    && chown -R www-data:www-data tmp logs \
    && chmod -R 775 tmp logs

EXPOSE 8080
CMD ["/entrypoint.sh"]
