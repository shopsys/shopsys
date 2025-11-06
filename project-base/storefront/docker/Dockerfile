FROM node:20-alpine3.17 AS base

ENV APP_ENV=production
ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

FROM base AS dependencies

# Check https://github.com/nodejs/docker-node/tree/b4117f9333da4138b03a546ec926ef50a31506c3#nodealpine to understand why libc6-compat might be needed.
RUN apk add --no-cache libc6-compat

RUN corepack enable
RUN corepack prepare --activate pnpm@9.6.0

WORKDIR /tmp/build
COPY package.json pnpm-lock.yaml ./

RUN pnpm install --frozen-lockfile
COPY . .

ARG SENTRY_URL
ARG SENTRY_RELEASE
ARG SENTRY_ORG
ARG SENTRY_AUTH_TOKEN
ARG SENTRY_PROJECT
ENV SENTRY_RELEASE=$SENTRY_RELEASE

RUN pnpm run build

FROM base AS production

USER node
ARG APP_DIR=/home/node/app
WORKDIR $APP_DIR

COPY --from=dependencies --chown=node:node /tmp/build/.next/standalone ./
COPY --from=dependencies --chown=node:node /tmp/build/.next/static ./.next/static
COPY --from=dependencies --chown=node:node /tmp/build/public ./public

CMD ["node", "server.js"]
