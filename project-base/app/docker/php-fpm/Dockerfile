FROM shopsys/php-image:16.0 as base

ARG project_root=.

# copy custom php.ini configuration
COPY ${project_root}/docker/php-fpm/php-ini-overrides.ini /usr/local/etc/php/php.ini

# Your can add your custom commands here (e.g. installing additional PHP extensions)

########################################################################################################################

FROM base AS production_builder
USER root

# Copy project source code
COPY --chown=www-data:www-data / /var/www/html

RUN rm -rf /var/www/html/node_modules \
    /var/www/html/tests

USER www-data
RUN composer install --optimize-autoloader --no-interaction --no-progress --no-dev \
    && php phing build-deploy-part-1-db-independent clean

#RUN chmod +x ${project_root}/deploy/deploy-project.sh && ${project_root}/deploy/deploy-project.sh merge

########################################################################################################################
##################################### Production stage #################################################################
########################################################################################################################
FROM base AS production

# Copy PHP-FPM configuration
COPY ${project_root}/docker/php-fpm/production-www.conf /usr/local/etc/php-fpm.d/www.conf

USER www-data
COPY --from=production_builder /var/www/html/ /var/www/html/

########################################################################################################################
##################################### Development stage ################################################################
########################################################################################################################
FROM base AS development
USER root
ARG www_data_uid
ARG www_data_gid

RUN apk add --no-cache \
    nodejs npm \
    htop \
    vim \
    nano \
    bash-completion \
    linux-headers \
    git \
    shadow

RUN if [ -n "${www_data_gid}" ] && ! getent group ${www_data_gid} >/dev/null; then \
        if getent group www-data >/dev/null; then groupmod -g ${www_data_gid} www-data; fi; \
    fi && \
    if [ -n "${www_data_uid}" ] && ! id -u ${www_data_uid} >/dev/null; then \
        if id -u www-data >/dev/null; then usermod -u ${www_data_uid} www-data; fi; \
    fi

COPY ${project_root}/docker/php-fpm/phing-completion /etc/bash_completion.d/phing

RUN echo "source /etc/profile.d/bash_completion.sh" >> ~/.bash_profile && \
    chown -R www-data:www-data /home/www-data /var/www/html

USER www-data
######################################################################################################################## \
##################################### Development with x debug stage ########################################################################
########################################################################################################################
FROM development AS development_x_debug
USER root
RUN pecl install xdebug-3.2.2 \
    && docker-php-ext-enable xdebug
USER www-data

########################################################################################################################
##################################### Development with blackfire ##########################
########################################################################################################################
FROM development AS development_blackfire
USER root
RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
&& architecture=$(uname -m) \
&& curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/alpine/$architecture/$version \
&& mkdir -p /tmp/blackfire \
&& tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
&& mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
&& printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8307\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
&& rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

RUN mkdir -p /tmp/blackfire \
    && architecture=$(uname -m) \
    && curl -A "Docker" -L https://blackfire.io/api/v1/releases/cli/linux/$architecture | tar zxp -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
    && rm -Rf /tmp/blackfire


# Please note that the Blackfire Probe is dependent on the session module.
# If it isn't present in your install, you will need to enable it yourself.
USER www-data
