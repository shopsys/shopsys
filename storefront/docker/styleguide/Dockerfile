FROM node:16-alpine as base

WORKDIR /app

FROM base as ci
COPY . /app

CMD npm install --legacy-peer-deps && npm run styleguide-server

FROM base as development

CMD npm install --legacy-peer-deps && npm run styleguide-server
