FROM node:16.15.0-alpine as development

ARG APP_DIR=/home/node/app

RUN mkdir -p $APP_DIR && chown -R node:node $APP_DIR

USER node

WORKDIR $APP_DIR

ENV NODE_ENV development

CMD npm ci --legacy-peer-deps && npm run dev
