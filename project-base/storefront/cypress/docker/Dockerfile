FROM cypress/base:18.16.0

WORKDIR /app

COPY --chown=node:node . /app

COPY ./docker/entrypoint.sh /

RUN rm -rf /app/node_modules

USER node
 
ENV TYPE=actual

ENTRYPOINT ["/entrypoint.sh"]
