FROM node:20-alpine3.17 AS base

RUN corepack enable
RUN corepack prepare --activate pnpm@8.10.5

FROM base as dependencies

# Check https://github.com/nodejs/docker-node/tree/b4117f9333da4138b03a546ec926ef50a31506c3#nodealpine to understand why libc6-compat might be needed.
RUN apk add --no-cache libc6-compat

WORKDIR /tmp/build
COPY package.json pnpm-lock.yaml ./

RUN pnpm install --frozen-lockfile
COPY . .

ARG SENTRY_URL
ARG SENTRY_RELEASE
ARG SENTRY_ORG
ARG SENTRY_AUTH_TOKEN
ARG SENTRY_PROJECT

ENV APP_ENV production
ENV NEXT_TELEMETRY_DISABLED 1
ENV SENTRY_RELEASE $SENTRY_RELEASE

RUN pnpm run build
RUN pnpm prune --prod
RUN rm -rf ./.next/cache

FROM base AS production
# Check https://github.com/nodejs/docker-node/tree/b4117f9333da4138b03a546ec926ef50a31506c3#nodealpine to understand why libc6-compat might be needed.
RUN apk add --no-cache libc6-compat

USER node
ARG APP_DIR=/home/node/app
WORKDIR $APP_DIR

ENV APP_ENV production
ENV NEXT_TELEMETRY_DISABLED 1

COPY --from=dependencies --chown=node:node /tmp/build $APP_DIR

CMD ["pnpm", "start"]
