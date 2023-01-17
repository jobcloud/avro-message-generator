FROM php:8.0-cli-alpine3.16

ARG USER_ID

RUN apk --no-cache upgrade && \
    apk --no-cache add bash git sudo autoconf gcc g++ make shadow librdkafka librdkafka-dev

RUN if [ -n "$USER_ID" ] && [ "$USER_ID" -lt 60001 ]; then \
        usermod -u ${USER_ID} -o www-data; \
    fi

RUN usermod -s /bin/bash www-data && \
    echo 'www-data ALL=(ALL) NOPASSWD: ALL' > '/etc/sudoers.d/www-data'

# USER: copy home
COPY --chown=www-data:www-data dev/php/files/user-home /home/www-data

# PHP: Copy configuration files & remove dist files
COPY dev/php/files/bin/ /usr/local/bin/
COPY dev/php/files/php/ /phpIni

# PHP: Install php extensions
RUN pecl channel-update pecl.php.net && \
    pecl install rdkafka-6.0.3 pcov && \
    php-ext-enable rdkafka pcov

# COMPOSER: install binary
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer --version=2.5.1

USER www-data

WORKDIR /var/www/html
