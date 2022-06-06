FROM php:8.1-fpm

# setup 'app' user mapped to host system user based on injected uid/gid
ARG uid
ARG gid
RUN groupadd -f -g ${gid} app && \
    useradd -u ${uid} -g ${gid} -s /bin/sh app && \
    install -d -o ${uid} -g ${gid} -m 755 /home/app

RUN apt-get update && apt-get install -qqy zlib1g-dev libzip-dev libicu-dev

RUN docker-php-ext-configure intl
RUN docker-php-ext-install zip intl sockets

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN cd /opt && \
    curl -fsSL https://deb.nodesource.com/setup_16.x | bash - && apt-get install -qqy nodejs

WORKDIR /code
