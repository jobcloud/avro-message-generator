FROM php:7.4-cli-alpine3.12

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

ARG USER_ID

# SYS: install required packages
RUN apk --no-cache upgrade && \
    apk --no-cache add autoconf bash git gcc g++ make shadow

RUN apk add librdkafka librdkafka-dev \
        --update-cache --repository http://dl-3.alpinelinux.org/alpine/edge/community

RUN if [ -n "$USER_ID" ] && [ "$USER_ID" -lt 60001 ]; then \
        usermod -u ${USER_ID} -o www-data; \
    fi

# PHP: copy bin
COPY files/bin/ /usr/local/bin/

# PHP: Install php extensions
RUN pecl channel-update pecl.php.net && \
    pecl install rdkafka && \
    docker-php-ext-install pcntl && \
    php-ext-enable rdkafka

USER www-data

# COMPOSER: install prestissimo for composer parallelism
RUN composer global require hirak/prestissimo

WORKDIR /var/www/html

ENTRYPOINT ["composer"]