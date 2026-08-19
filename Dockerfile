# B-Stock (Laravel 10.25 / Mix React) — image pour Render
# Frontend : Laravel Mix 6 (pas Vite)
# Runtime : Nginx + PHP-FPM 8.2 (un seul process principal : supervisord)

FROM node:18-bookworm-slim AS frontend
WORKDIR /app
ENV NODE_OPTIONS=--max-old-space-size=2048
COPY package.json package-lock.json ./
RUN npm ci --legacy-peer-deps
COPY webpack.mix.js ./
COPY resources ./resources
COPY public ./public
RUN npm run production

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

FROM php:8.2-fpm-bookworm
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        gettext-base \
        git \
        unzip \
        curl \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf

COPY docker/php.ini /usr/local/etc/php/conf.d/bstock.ini
COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /usr/local/bin/start.sh

RUN sed -i 's/listen = 9000/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'clear_env = no' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && chmod +x /usr/local/bin/start.sh \
    && sed -i 's/\r$//' /usr/local/bin/start.sh \
    && mkdir -p /var/lib/nginx/body /run/nginx \
    && chown -R www-data:www-data /var/lib/nginx /run/nginx

COPY --from=vendor /app /var/www/html
COPY --from=frontend /app/public/js /var/www/html/public/js
COPY --from=frontend /app/public/mix-manifest.json /var/www/html/public/mix-manifest.json

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    QUEUE_CONNECTION=sync \
    CACHE_DRIVER=file \
    SESSION_DRIVER=cookie \
    QUEUE_CONVERSIONS_BY_DEFAULT=false

EXPOSE 10000
CMD ["/usr/local/bin/start.sh"]
