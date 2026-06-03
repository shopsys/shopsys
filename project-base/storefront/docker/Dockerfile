FROM node:24.14.0-alpine3.22 AS base

ENV COREPACK_HOME=/usr/local/share/corepack
RUN apk add --no-cache icu-data-full libc6-compat
RUN corepack enable && corepack prepare pnpm@10.30.3 --activate

FROM base AS dependencies

WORKDIR /tmp/build
COPY package.json pnpm-lock.yaml ./

RUN set -eux; \
    for i in 1 2 3 4 5; do \
        pnpm install --frozen-lockfile && break; \
        if [ "$i" -eq 5 ]; then \
            exit 1; \
        fi; \
        sleep_time=$((i * 5)); \
        echo "pnpm install failed (attempt ${i}/5), retrying in ${sleep_time}s..."; \
        sleep "$sleep_time"; \
    done
COPY . .

ARG SENTRY_URL
ARG SENTRY_RELEASE
ARG SENTRY_ORG
ARG SENTRY_AUTH_TOKEN
ARG SENTRY_PROJECT
ARG CYPRESS_KEEP_TID

ENV APP_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1
ENV SENTRY_RELEASE=$SENTRY_RELEASE
ENV CYPRESS_KEEP_TID=$CYPRESS_KEEP_TID

RUN pnpm run build
RUN pnpm prune --prod
RUN rm -rf ./.next/cache

FROM base AS production

ARG APP_DIR=/home/node/app
WORKDIR $APP_DIR

ENV APP_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

COPY --from=dependencies /tmp/build $APP_DIR
RUN chown -R root:root $APP_DIR \
    && chmod -R a-w $APP_DIR \
    && mkdir -p $APP_DIR/.next/cache \
    && chown -R node:node $APP_DIR/.next/cache

USER node

CMD ["pnpm", "start"]
