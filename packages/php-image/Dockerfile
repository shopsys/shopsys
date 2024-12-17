ARG LINUX_DISTRIBUTION=alpine
ARG project_root='/'
ARG NODE_MAJOR=20
ARG PHP_VERSION=8.3

# Node and Composer are already installed in the node_builder and composer_builder stages
FROM composer:latest AS composer_builder
FROM node:${NODE_MAJOR}-${LINUX_DISTRIBUTION} AS node_builder


FROM php:${PHP_VERSION}-fpm-${LINUX_DISTRIBUTION} AS base

# Build dependencies needed for installation and cleanup
#  autoconf - Required for compiling extensions
#  freetype-dev - Development files for FreeType (font rendering) needed for gd extension
#  g++ - Compiler for gd extension
#  icu-dev - Development files for ICU (International Components for Unicode) needed for intl extension
#  jpeg-dev - Development files for JPEG (image format) needed for gd extension
#  libpng-dev - Development files for PNG (image format) needed for gd extension
#  libpq-dev - Development files for PostgreSQL needed for pdo_pgsql and pgsql extensions
#  libzip-dev - Development files for libzip needed for zip extension
#  make - Required for compiling extensions
#  openssl-dev - Development files for OpenSSL
#  rabbitmq-c-dev - Development files for RabbitMQ
#  bcmath - Arbitrary precision mathematics
#  gd - Image processing for gd extension
#  intl - Internationalization for intl extension
#  opcache - Opcode cache for performance
#  pdo_pgsql - PostgreSQL driver for PDO
#  pgsql - PostgreSQL driver
#  zip - Zip archive handling for zip extension
#  bash - Shell for running scripts
#  ca-certificates - Certificates for secure connections
#  coreutils - Basic file, shell and text manipulation utilities
#  freetype - Font rendering for gd extension
#  htop - Interactive process viewer for monitoring
#  icu-data-full - ICU data for full locale support for intl extension
#  icu-libs - ICU libraries for intl extension
#  jpeg - Image format for gd extension
#  libpng - Image format for gd extension
#  libzip - Zip archive handling for zip extension
#  musl-locales - Locales for internationalization
#  nano - Text editor for editing files
#  openssl - Secure communication for OpenSSL
#  postgresql-client - PostgreSQL client for connecting to databases
#  rabbitmq-c - RabbitMQ client for connecting to message broker
#  vim - Text editor for editing files

RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    freetype-dev \
    g++ \
    icu-dev \
    jpeg-dev \
    libpng-dev \
    libpq-dev \
    libzip-dev \
    make \
    openssl-dev \
    rabbitmq-c-dev && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    pecl install amqp && \
    docker-php-ext-enable amqp && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
        bcmath \
        gd \
        intl \
        opcache \
        pdo_pgsql \
        pgsql \
        zip && \
    apk del .build-deps && \
    apk add --no-cache \
        bash \
        bash-completion \
        ca-certificates \
        coreutils \
        freetype \
        htop \
        icu-data-full \
        icu-libs \
        jpeg \
        libpng \
        libzip \
        musl-locales \
        nano \
        openssl \
        postgresql15-client \
        rabbitmq-c \
        vim

ENV LANG=en_US.UTF-8
ENV LANGUAGE=en_US:en
ENV LC_ALL=en_US.UTF-8

# Copy Composer from composer_builder stage
COPY --from=composer_builder /usr/bin/composer /usr/local/bin/composer

# Symbolic link for Node.js
COPY --from=node_builder \
    /usr/local/bin/node \
    /usr/local/bin/npm \
    /usr/local/bin/
COPY --from=node_builder /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node_builder /usr/local/include/node /usr/local/include/node
RUN if [ -e "/usr/local/bin/npm" ]; then rm /usr/local/bin/npm; fi && ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

COPY ./docker-php-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-entrypoint
RUN chown -R www-data:www-data /var/www/html

COPY ./phing-completion /etc/bash_completion.d/phing

RUN echo "source /etc/profile.d/bash_completion.sh" >> ~/.bash_profile
USER www-data

RUN mkdir -p /var/www/html/.npm-global
ENV NPM_CONFIG_PREFIX=/var/www/html/.npm-global
