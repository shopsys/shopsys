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
RUN apt-get update && apt-get -y install postgresql-client-10

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

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
    && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > /etc/php/7.2/fpm/conf.d/blackfire.ini

RUN apt-get update && apt-get -y install nodejs
RUN apt-get update && apt-get -y install npm

# Install grunt-cli using npm to be able to run grunt watch.
RUN npm install -g grunt-cli

WORKDIR "/var/www/shopsys-framework"