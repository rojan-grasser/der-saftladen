FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache bash libpng libpng-dev oniguruma-dev icu-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo_mysql mbstring intl xml opcache pdo_pgsql pcntl

COPY . .

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

RUN apk add --no-cache ca-certificates tar \
    && curl -fL \
       https://github.com/roadrunner-server/roadrunner/releases/download/v2025.1.6/roadrunner-2025.1.6-unknown-musl-amd64.tar.gz \
       -o /tmp/rr.tar.gz \
    && tar -xzf /tmp/rr.tar.gz -C /tmp \
    && mv /tmp/roadrunner-2025.1.6-unknown-musl-amd64/rr /usr/local/bin/rr \
    && chmod +x /usr/local/bin/rr \
    && rm -rf /tmp/rr.tar.gz /tmp/roadrunner-2025.1.6-unknown-musl-amd64 \
    && /usr/local/bin/rr --version

RUN apk add --no-cache wget unzip \
    && wget -q https://releases.hashicorp.com/terraform/1.10.5/terraform_1.10.5_linux_amd64.zip -O /tmp/terraform.zip \
    && unzip /tmp/terraform.zip -d /usr/local/bin \
    && rm /tmp/terraform.zip \
    && terraform --version

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENV RUNMODE server

ENTRYPOINT ["docker-entrypoint.sh"]
