FROM node:16.15.0-alpine as production

# Check https://github.com/nodejs/docker-node/tree/b4117f9333da4138b03a546ec926ef50a31506c3#nodealpine to understand why libc6-compat might be needed.
RUN apk add --no-cache libc6-compat

ARG APP_DIR=/home/node/app

RUN mkdir -p $APP_DIR && chown -R node:node $APP_DIR

USER node

WORKDIR $APP_DIR

COPY --chown=node:node . .

# CI = clear install, don't try to update dependencies
RUN npm ci --no-dev

ENV NODE_ENV production
ENV NEXT_TELEMETRY_DISABLED 1

RUN npm run build

CMD npm run start
