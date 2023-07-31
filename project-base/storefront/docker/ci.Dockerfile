FROM node:18.15.0-alpine as development

RUN corepack enable
RUN corepack prepare --activate pnpm@8.6.7

ARG APP_DIR=/home/node/app
WORKDIR $APP_DIR

ENV APP_ENV development
ENV NEXT_TELEMETRY_DISABLED 1

COPY docker/entrypoint.sh /
ENTRYPOINT ["/entrypoint.sh"]

CMD ["dev"]
