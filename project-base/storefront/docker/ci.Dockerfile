FROM node:24.14.0-alpine3.22 AS development

RUN apk add --no-cache icu-data-full libc6-compat
RUN corepack enable && corepack prepare pnpm@10.30.3 --activate

ARG APP_DIR=/home/node/app
WORKDIR $APP_DIR

ENV APP_ENV=development
ENV NEXT_TELEMETRY_DISABLED=1

COPY docker/entrypoint.sh /
ENTRYPOINT ["/entrypoint.sh"]

RUN mkdir -p "$APP_DIR" && chown -R node:node /home/node
USER node

CMD ["dev"]
