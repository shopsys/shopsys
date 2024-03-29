FROM shopsys/php-image:15.0 as base

ARG project_root=.

# copy custom php.ini configuration
COPY ${project_root}/docker/php-fpm/php-ini-overrides.ini /usr/local/etc/php/php.ini

# Your can add your custom commands here (e.g. installing additional PHP extensions)

########################################################################################################################

FROM base as development

USER root

# allow overwriting UID and GID o the user "www-data" to help solve issues with permissions in mounted volumes
# if the GID is already in use, we will assign GID 33 instead (33 is the standard uid/gid for "www-data" in Debian)
ARG www_data_uid
ARG www_data_gid
RUN if [ -n "$www_data_uid" ]; then deluser www-data && (addgroup --gid $www_data_gid www-data || addgroup --gid 33 www-data) && adduser --system --home /home/www-data --uid $www_data_uid --disabled-password --group www-data; fi;

# as the UID and GID might have changed, change the ownership of the home directory workdir again
RUN chown -R www-data:www-data /home/www-data /var/www/html

USER www-data

########################################################################################################################

FROM base as production

ARG project_root=.

# copy custom FPM pool configuration
COPY ${project_root}/docker/php-fpm/production-www.conf /usr/local/etc/php-fpm.d/www.conf

COPY --chown=www-data:www-data / /var/www/html

RUN composer install --optimize-autoloader --no-interaction --no-progress --no-dev

RUN php phing build-deploy-part-1-db-independent clean

########################################################################################################################

FROM production as production-project

RUN chmod +x ./deploy/deploy-project.sh && ./deploy/deploy-project.sh merge

########################################################################################################################

FROM base as ci

COPY --chown=www-data:www-data / /var/www/html

RUN curl -L \
      -H "Accept: application/vnd.github+json" \
      -H "X-GitHub-Api-Version: 2022-11-28" \
      https://api.github.com/rate_limit

RUN composer install --optimize-autoloader --no-interaction --no-progress --dev -vvv

RUN php phing build-deploy-part-1-db-independent

RUN ./bin/console shopsys:environment:change prod

RUN php phing clean
