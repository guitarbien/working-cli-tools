#-----------------------------------
FROM php:7.3-alpine AS base

# 全域設定
#WORKDIR /source

# 安裝環境
RUN apk add --no-cache unzip

# 安裝 extension
RUN set -xe && \
        apk add --no-cache --virtual .build-deps \
            autoconf \
            g++ \
            make \
        && \
            docker-php-ext-install \
                bcmath \
        && \
            pecl install \
                redis \
        && \
            docker-php-ext-enable \
                redis \
        && \
            apk del .build-deps \
        && \
            php -m

#-----------------------------------
FROM base AS composer_builder

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 安裝程式依賴套件
COPY composer.* ./
RUN composer install --no-dev --no-scripts && composer clear-cache

## laravel 相關基礎設定
#RUN php -r "file_exists('.env') || copy('.env.example', '.env');"
#RUN php artisan key:generate

#-----------------------------------
FROM base

COPY --from=composer_builder /source/vendor ./vendor
COPY . .
#CMD ["php", "artisan", "serve", "--host", "0.0.0.0"]

# Code file to execute when the docker container starts up (`entrypoint.sh`)
ENTRYPOINT ["/entrypoint.sh"]
