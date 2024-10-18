ARG LINUX_VERSION=alpine
ARG project_root='/'
ARG NODE_MAJOR=20
ARG PHP_VERSION=8.3

# Node and Composer are already installed in the node_builder and composer_builder stages
FROM composer:2.7 AS composer_builder
FROM node:${NODE_MAJOR}-${LINUX_VERSION} AS node_builder


FROM php:${PHP_VERSION}-fpm-${LINUX_VERSION} AS base


# Build dependencies needed for installation and cleanup
RUN apk add --no-cache \
    autoconf \
    g++ \
    make \
    rabbitmq-c-dev \
    icu-dev \
    libpq-dev \
    libzip-dev \
    openssl-dev

RUN pecl install redis-5.3.7 && \
    docker-php-ext-enable redis

RUN pecl install amqp && \
    docker-php-ext-enable amqp

RUN docker-php-ext-install zip

RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pgsql
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install intl
RUN docker-php-ext-install opcache
# RUN apk del .build-deps

RUN apk add --no-cache \
        bash \
        coreutils \
        icu-libs \
        icu-data-full \
        libzip \
        openssl \
        ca-certificates \
        postgresql-client \
        rabbitmq-c \
        musl-locales

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

USER www-data

RUN mkdir -p /var/www/html/.npm-global
ENV NPM_CONFIG_PREFIX /var/www/html/.npm-global
