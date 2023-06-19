FROM cypress/base:16.5.0

ENV CYPRESS_BASE_URL=http://webserver:8080/

WORKDIR /app

COPY . /app

CMD whoami && ls -la && npm ci && npx cypress run
