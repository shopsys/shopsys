FROM node:16-alpine as base

WORKDIR /app

#=====================================================================
FROM base as development

CMD npm install --legacy-peer-deps && npm run dev

#=====================================================================
FROM base as production

COPY /storefront /app
RUN npm install --no-dev

RUN npm run build
CMD npm run start
