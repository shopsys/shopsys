FROM cypress/base:18.16.0

WORKDIR /app

COPY . /app

COPY ./docker/entrypoint.sh /

RUN npm ci --force

ENV TYPE=actual

ENTRYPOINT ["/entrypoint.sh"]
