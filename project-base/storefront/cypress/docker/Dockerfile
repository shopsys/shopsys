FROM cypress/base:18.16.0

WORKDIR /app

COPY --chown=node:node . /app

COPY ./docker/entrypoint.sh /

RUN rm -rf /app/node_modules

RUN chown node:node /app

USER node

RUN npm ci --force

ENV TYPE=actual

ENTRYPOINT ["/entrypoint.sh"]
