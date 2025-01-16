FROM node:23-alpine3.21 AS base

# Check https://github.com/nodejs/docker-node/tree/b4117f9333da4138b03a546ec926ef50a31506c3#nodealpine to understand why libc6-compat might be needed.
RUN apk add --no-cache libc6-compat

FROM base AS dependencies

RUN corepack enable
RUN corepack prepare --activate pnpm@9.0.5

WORKDIR /tmp/build
COPY package.json pnpm-lock.yaml ./

RUN pnpm install --frozen-lockfile --prod
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

FROM base AS production

USER node
WORKDIR /app

ENV APP_ENV production
ENV NEXT_TELEMETRY_DISABLED 1

COPY --from=dependencies /tmp/build/.next/standalone ./
COPY --from=dependencies /tmp/build/public ./public
COPY --from=dependencies /tmp/build/.next/static ./.next/static

EXPOSE 3000
CMD ["node", "server.js"]
