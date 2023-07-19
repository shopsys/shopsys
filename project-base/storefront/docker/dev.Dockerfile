FROM node:18.15.0-alpine as development

RUN corepack enable
RUN corepack prepare --activate pnpm@8.6.7

ARG HOME_DIR=/home/node
ARG APP_DIR=$HOME_DIR/app

# Ensure that files are mounted with the correct permissions
ARG node_uid
RUN apk add --no-cache shadow

RUN if [[ -n "$node_uid" && "$node_uid" -ne 1000 ]]; then usermod -u $node_uid node && chown -R node $HOME_DIR; fi;

USER node
WORKDIR $APP_DIR

ENV NODE_ENV development
ENV NEXT_TELEMETRY_DISABLED 1

COPY docker/entrypoint.sh /
ENTRYPOINT ["/entrypoint.sh"]

CMD ["dev"]
