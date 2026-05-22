FROM node:24.14.0-alpine3.22 AS development

ENV COREPACK_HOME=/usr/local/share/corepack
RUN apk add --no-cache icu-data-full libc6-compat
RUN corepack enable && corepack prepare pnpm@10.30.3 --activate

ARG HOME_DIR=/home/node
ARG APP_DIR=$HOME_DIR/app

# Ensure that files are mounted with the correct permissions
ARG node_uid
RUN apk add --no-cache shadow

RUN if [[ -n "$node_uid" && "$node_uid" -ne 1000 ]]; then usermod -u $node_uid node && chown -R node $HOME_DIR; fi;

USER node
WORKDIR $APP_DIR

ENV APP_ENV=development
ENV NEXT_TELEMETRY_DISABLED=1

COPY docker/entrypoint.sh /
ENTRYPOINT ["/entrypoint.sh"]

CMD ["dev"]
