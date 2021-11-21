FROM node:16-alpine as base

WORKDIR /app

#=====================================================================

FROM base as production

COPY . /app

# CI = clear install, don't try to update dependencies
RUN npm ci --no-dev

RUN npm run build
CMD npm run start

#=====================================================================
FROM base as development

RUN apk --no-cache add curl

CMD npm ci --legacy-peer-deps && npm run dev

#=====================================================================

