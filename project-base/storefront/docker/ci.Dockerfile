FROM node:20-alpine3.17 as development

RUN corepack enable
RUN corepack prepare --activate pnpm@9.0.5

ARG APP_DIR=/home/node/app
WORKDIR $APP_DIR

ENV APP_ENV development
ENV NEXT_TELEMETRY_DISABLED 1

COPY docker/entrypoint.sh /
ENTRYPOINT ["/entrypoint.sh"]

CMD ["dev"]
