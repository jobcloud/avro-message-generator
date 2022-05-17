FROM php:7.4-cli-alpine3.13

ARG USER_ID

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# SYS: install required packages
RUN apk --no-cache upgrade && \
    apk --no-cache add autoconf bash git gcc g++ make shadow

RUN apk add librdkafka librdkafka-dev \
        --update-cache --repository http://dl-3.alpinelinux.org/alpine/edge/community

RUN /bin/bash -c 'if [ -n $USER_ID ] && [ $USER_ID -lt 60000 ]; then \
        usermod -u ${USER_ID} www-data; \
    fi'

# PHP: copy bin
COPY files/bin/ /usr/local/bin/

# PHP: Install php extensions
RUN pecl channel-update pecl.php.net && \
    pecl install rdkafka-6.0.1 && \
    docker-php-ext-install pcntl && \
    php-ext-enable rdkafka

USER www-data

WORKDIR /var/www/html

ENTRYPOINT ["composer"]
