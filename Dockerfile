FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache bash libpng libpng-dev oniguruma-dev icu-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring intl xml opcache

COPY . .

COPY vendor ./vendor

COPY public/build ./public/build

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
