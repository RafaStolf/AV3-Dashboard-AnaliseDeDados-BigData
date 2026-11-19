# syntax=docker/dockerfile:1
FROM node:24-bookworm-slim AS assets
WORKDIR /build
COPY package.json package-lock.json .npmrc ./
RUN npm ci --ignore-scripts --include=optional
COPY resources ./resources
COPY public ./public
COPY tsconfig.json vite.config.ts ./
RUN npm run build

FROM php:8.4-fpm-bookworm AS php-base
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev libzip-dev unzip gosu libfcgi-bin \
    && docker-php-ext-install -j"$(nproc)" intl zip \
    && php -r 'foreach (["pdo_sqlite", "sqlite3", "mbstring", "intl", "zip", "Zend OPcache"] as $ext) { if (!extension_loaded($ext)) { throw new RuntimeException("Missing extension: ".$ext); } }' \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY deploy/php.ini /usr/local/etc/php/conf.d/production.ini
COPY deploy/php-fpm.conf /usr/local/etc/php-fpm.d/zz-atlas.conf

FROM php-base AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY . .
RUN mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

FROM php-base AS app
COPY --from=dependencies /var/www/html /var/www/html
COPY --from=assets /build/public/build /var/www/html/public/build
RUN chmod +x deploy/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache
ENTRYPOINT ["/var/www/html/deploy/entrypoint.sh"]
CMD ["php-fpm", "-F"]
HEALTHCHECK --interval=15s --timeout=5s --start-period=120s --retries=5 \
    CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q pong

FROM nginx:stable-alpine AS nginx
COPY deploy/nginx.conf /etc/nginx/conf.d/default.conf
COPY deploy/fastcgi.conf /etc/nginx/atlas-fastcgi.conf
COPY --from=assets /build/public /var/www/html/public
EXPOSE 8080
HEALTHCHECK --interval=15s --timeout=5s --start-period=15s --retries=5 \
    CMD wget -q -O /dev/null http://127.0.0.1:8080/up || exit 1
