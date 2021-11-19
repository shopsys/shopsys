FROM cypress/base:16.5.0 as base

WORKDIR /app

COPY . /app

RUN npm install cypress

CMD npx cypress run
