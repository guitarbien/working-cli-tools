FROM php:7.3-alpine

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

RUN set -xe && \
        curl -sS https://getcomposer.org/installer | php && \
        mv composer.phar /usr/local/bin/composer

# 安裝程式依賴套件
COPY composer.* ./
RUN composer install --no-dev --no-scripts && composer clear-cache

# laravel 相關基礎設定
RUN php -r "file_exists('.env') || copy('.env.example', '.env');"
#RUN php artisan key:generate

# 複製程式碼
COPY . .
#RUN composer run post-autoload-dump

#CMD ["php", "artisan", "serve", "--host", "0.0.0.0"]

# Copies your code file from your action repository to the filesystem path `/` of the container
COPY entrypoint.sh /entrypoint.sh

# Code file to execute when the docker container starts up (`entrypoint.sh`)
ENTRYPOINT ["/entrypoint.sh"]
