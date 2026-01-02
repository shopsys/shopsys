FROM cypress/base:20.18.0

WORKDIR /app

COPY . /app

COPY ./docker/entrypoint.sh /

RUN npm ci --force

ENV TYPE=regression
ENV COMMAND=run

ENTRYPOINT ["/entrypoint.sh"]
