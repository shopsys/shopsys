FROM cypress/base:18.16.0

WORKDIR /app

COPY --chown=node:node . /app

USER node

CMD npm ci && npx cypress run 
