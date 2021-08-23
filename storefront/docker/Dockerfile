FROM node:16-alpine as base

WORKDIR /app

#=====================================================================
FROM base as development

RUN apk --no-cache add curl

CMD npm install --legacy-peer-deps && npm run dev

#=====================================================================
FROM base as production

COPY . /app

# CI = clear install, don't try to update dependencies
RUN npm ci --no-dev

RUN npm run build
CMD npm run start
