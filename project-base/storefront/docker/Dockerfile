FROM node:18.15.0-alpine AS base

RUN corepack enable
RUN corepack prepare --activate pnpm@8.4.0

FROM base AS dependecies

WORKDIR /tmp/build
COPY package.json pnpm-lock.yaml ./

RUN pnpm install --frozen-lockfile
COPY . .

ARG SENTRY_RELEASE
ENV NODE_ENV production
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

COPY --from=dependecies --chown=node:node /tmp/build $APP_DIR

CMD ["pnpm", "start"]
