FROM node:16.15.0-alpine as deps

WORKDIR /tmp/build
COPY package.json package-lock.json ./

RUN npm ci
COPY . .

ARG SENTRY_RELEASE
ENV NODE_ENV production
ENV NEXT_TELEMETRY_DISABLED 1
ENV SENTRY_RELEASE $SENTRY_RELEASE

RUN npm run build
RUN npm ci --production
RUN rm -rf ./.next/cache

FROM node:16.15.0-alpine AS production
# Check https://github.com/nodejs/docker-node/tree/b4117f9333da4138b03a546ec926ef50a31506c3#nodealpine to understand why libc6-compat might be needed.
RUN apk add --no-cache libc6-compat

USER node
ARG APP_DIR=/home/node/app
WORKDIR $APP_DIR

COPY --from=deps --chown=node:node /tmp/build $APP_DIR

CMD ["npm", "start"]
