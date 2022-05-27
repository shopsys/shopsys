FROM node:16.15.0-alpine as development

ARG APP_DIR=/home/node/app

USER node

WORKDIR $APP_DIR

ENV NODE_ENV development

RUN npm ci --legacy-peer-deps

CMD npm run dev
