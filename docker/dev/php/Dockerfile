FROM php:7.4-cli-alpine3.12

ARG USER_ID

RUN apk --no-cache upgrade && \
    apk --no-cache add bash git sudo autoconf gcc g++ make shadow

RUN apk add librdkafka librdkafka-dev \
        --update-cache --repository http://dl-3.alpinelinux.org/alpine/edge/community

RUN if [ -n "$USER_ID" ] && [ "$USER_ID" -lt 60001 ]; then \
        usermod -u ${USER_ID} -o www-data; \
    fi

RUN usermod -s /bin/bash www-data && \
    echo 'www-data ALL=(ALL) NOPASSWD: ALL' > '/etc/sudoers.d/www-data'

# USER: copy home
COPY --chown=www-data:www-data dev/php/files/user-home /home/www-data

# PHP: Copy configuration files
COPY dev/php/files/bin/ /usr/local/bin/

# PHP: Install php extensions
RUN mkdir /phpIni && \
    pecl channel-update pecl.php.net && \
    pecl install rdkafka pcov && \
    php-ext-enable rdkafka pcov

# COMPOSER: install binary
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

USER www-data

# COMPOSER: install hirak for parallelism
RUN composer global require hirak/prestissimo

WORKDIR /var/www/html
