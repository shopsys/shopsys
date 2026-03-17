FROM node:24.14.0-alpine3.22 AS development

RUN apk add --no-cache icu-data-full libc6-compat
RUN corepack enable && corepack prepare pnpm@10.30.3 --activate

ARG HOME_DIR=/home/node
ARG APP_DIR=$HOME_DIR/app

USER node
WORKDIR $APP_DIR

ENV APP_ENV=development
ENV NEXT_TELEMETRY_DISABLED=1

COPY docker/entrypoint.sh /
ENTRYPOINT ["/entrypoint.sh"]

CMD ["dev"]
