FROM node:16.5.0-buster as base

WORKDIR /app

FROM base as production

COPY . /app

# CI = clear install, don't try to update dependencies
RUN npm ci --no-dev

RUN npm run build
CMD npm run start

#=====================================================================

FROM base as development

# allow overwriting UID and GID of the user "www-data" to help solve issues with permissions in mounted volumes
# if the GID is already in use, we will assign GID 33 instead (33 is the standard uid/gid for "www-data" in Debian)
ARG www_data_uid
ARG www_data_gid

RUN if [ $www_data_uid -eq 1000 ]; then deluser node; fi;

RUN if [ -n "$www_data_uid" ]; then deluser www-data && (addgroup --gid $www_data_gid www-data || addgroup --gid 33 www-data) && adduser --system --home /home/www-data --uid $www_data_uid --disabled-password --group www-data; fi;

# as the UID and GID might have changed, change the ownership of the home directory workdir again
RUN chown -R www-data:www-data /app

USER www-data

CMD npm ci --legacy-peer-deps && npm run dev
