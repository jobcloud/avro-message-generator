FROM php:8.0-cli-alpine3.16

ARG USER_ID

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# SYS: install required packages
RUN apk --no-cache upgrade && \
    apk --no-cache add autoconf bash git gcc g++ make shadow librdkafka librdkafka-dev

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
