FROM node:16.15.0-alpine as development

ARG APP_DIR=/home/node/app

RUN mkdir -p $APP_DIR && chown -R node:node $APP_DIR

USER node

WORKDIR $APP_DIR

COPY --chown=node:node . .

ENV NODE_ENV development

RUN npm ci --legacy-peer-deps

CMD npm run dev
