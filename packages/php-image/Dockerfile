ARG DEBIAN_VERSION=bullseye
ARG NODE_MAJOR=20

FROM php:8.3-fpm-${DEBIAN_VERSION}

ARG NODE_MAJOR
ARG DEBIAN_VERSION

# apt-utils so package configuartion does not get delayed
# autoconf needed to install "Redis" extension
# bash-completion for Phing target completion
# ca-certificates to ensure certificates are up to date
# cron to be able to schedule and automate recurring tasks and jobs
# gnupg and g++ for gd extension
# git for computing diffs and for npm to download packages
# htop for quick monitoring
# libfreetype6-dev needed by "gd" extension
# libicu-dev for intl extension
# libjpeg-dev needed by "gd" extension
# libpng-dev needed by "gd" extension
# libpg-dev for connection to postgres database
# librabbitmq-dev for connection to rabbitmq
# libzip-dev needed by "zip" extension
# locales for locale-gen command
# mc for quick file browsing
# nano for quick file editing
# unzip to ommit composer zip packages corruption
# vim for quick file editing
# wget for installation of other tools
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        apt-utils \
        autoconf \
        bash-completion \
        ca-certificates \
        cron \
        g++ \
        git \
        gnupg \
        htop \
        libfreetype6-dev \
        libicu-dev \
        libjpeg-dev \
        libpng-dev \
        libpq-dev \
        librabbitmq-dev \
        libzip-dev \
        locales \
        mc \
        nano \
        unzip \
        vim \
        wget && \
    apt-get clean

# Install NodeJS
RUN mkdir -p /etc/apt/keyrings && \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list && \
    apt-get update && apt-get install -y --no-install-recommends nodejs && apt-get clean

# install Composer
COPY ./docker-install-composer /usr/local/bin/docker-install-composer
RUN chmod +x /usr/local/bin/docker-install-composer && docker-install-composer

# install necessary php extensions for running application
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
        bcmath \
        fileinfo \
        gd \
        intl \
        opcache \
        pgsql \
        pdo_pgsql \
        zip

# install PostgreSQl client for dumping database
RUN curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /etc/apt/keyrings/postgresql.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/postgresql.gpg] https://apt.postgresql.org/pub/repos/apt/ $DEBIAN_VERSION-pgdg main" | tee /etc/apt/sources.list.d/PostgreSQL.list && \
    apt-get update && apt-get install -y --no-install-recommends postgresql-client-12 && apt-get clean

# install redis extension
RUN pecl install redis-5.3.7 && \
    docker-php-ext-enable redis

# install amqp extension
RUN pecl install amqp \
    && docker-php-ext-enable amqp

# install locales and switch to en_US.utf8 in order to enable UTF-8 support
# see http://jaredmarkell.com/docker-and-locales/ from where was this code taken
RUN sed -i -e 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen && \
    locale-gen
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

# add bash completion for phing
COPY ./phing-completion /etc/bash_completion.d/phing

# overwrite the original entry-point from the PHP Docker image with our own
COPY ./docker-php-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-entrypoint

# set www-data user his home directory
# the user "www-data" is used when running the image, and therefore should own the workdir
RUN usermod -m -d /home/www-data www-data && \
    mkdir -p /var/www/html && \
    chown -R www-data:www-data /home/www-data /var/www/html

# Switch to user
USER www-data

# enable bash completion
RUN echo "source /etc/bash_completion" >> ~/.bashrc

RUN mkdir -p /var/www/html/.npm-global
ENV NPM_CONFIG_PREFIX /var/www/html/.npm-global
