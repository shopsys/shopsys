FROM phpdockerio/php72-fpm:latest

# Switch to non-interactive mode during image build to silence some warnings.
# See https://github.com/moby/moby/issues/4032
ARG DEBIAN_FRONTEND=noninteractive

# Install packages are useful when working in the container in interactive mode.
RUN apt-get update && apt-get -y install apt-utils dialog

# Install ACL support (ie. tools like "setfacl").
RUN apt-get update && apt-get -y install acl

# Install Git - it is necessary for code diffs
RUN apt-get update && apt-get -y install git

# Install PostgreSQL client for dumping database
RUN apt-get update && apt-get -y install postgresql-client-9.5

# Install locales and switch to en_US.utf8 in order to enable UTF-8 support.
# See https://perlgeek.de/en/article/set-up-a-clean-utf8-environment
RUN apt-get update && apt-get -y install locales-all
ENV LC_ALL=en_US.utf8 LANG=en_US.utf8 LANGUAGE=en_US.utf8

# Install required PHP extensions
RUN apt-get update && apt-get -y --no-install-recommends install \
        php7.2-bcmath \
        php7.2-ctype \
        php7.2-curl \
        php7.2-fpm \
        php7.2-gd \
        php7.2-iconv \
        php7.2-intl \
        php7.2-json \
        php7.2-mbstring \
        php7.2-opcache \
        php7.2-pdo \
        php7.2-pgsql \
        php7.2-redis \
        php7.2-simplexml \
        php7.2-soap \
        php7.2-xml \
        php7.2-zip

# Suggested PHP settings by Symfony and Shopsys Framework
COPY php-ini-overrides.ini /etc/php/7.2/fpm/conf.d/99-overrides.ini

# Install Composer
RUN php -r "copy('https://getcomposer.org/download/1.6.4/composer.phar', '/usr/local/bin/composer');" \
    && php -r "if (hash_file('SHA256', '/usr/local/bin/composer') === '8d0d9246f68532cc8fd6a47a58d195f0c849d26430f93c397a3176fc2d971ded') {echo 'Composer installed';} else {echo 'Hash invalid for downloaded composer.phar'; exit(1);}" \
    && chmod 0755 /usr/local/bin/composer \
    && /usr/local/bin/composer selfupdate --stable

# Install Node.js v6 from NodeSource repository that also contains NPM.
# See https://nodejs.org/en/download/package-manager/#debian-and-ubuntu-based-linux-distributions
RUN curl -sL https://deb.nodesource.com/setup_6.x | bash - \
    && apt-get update && apt-get -y install nodejs=6.*

# Install grunt-cli using npm to be able to run grunt watch.
RUN npm install -g grunt-cli

WORKDIR "/var/www/shopsys-framework"
