FROM cypress/base:18.16.0

ENV CYPRESS_BASE_URL=http://webserver:8080/

WORKDIR /app

COPY . /app

CMD npm ci && npx cypress run
