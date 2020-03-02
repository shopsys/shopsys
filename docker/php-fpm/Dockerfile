FROM pk16011990/shopsys-framework:9.0-dev1 as base

USER www-data

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

# copy FPM pool configuration
COPY ${project_root}/docker/php-fpm/production-www.conf /usr/local/etc/php-fpm.d/www.conf

COPY --chown=www-data:www-data / /var/www/html

RUN composer install --optimize-autoloader --no-interaction --no-progress --no-dev

RUN php phing build-deploy-part-1-db-independent clean

########################################################################################################################

FROM base as ci

COPY --chown=www-data:www-data / /var/www/html

RUN composer install --optimize-autoloader --no-interaction --no-progress --dev

RUN php phing composer-dev dirs-create test-dirs-create assets npm standards tests-unit tests-acceptance-build

RUN ./bin/console shopsys:environment:change prod
