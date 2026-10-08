ARG NODE_VERSION=24.14.0
ARG CHROME_FOR_TESTING_VERSION=155.0.8059.39
FROM cypress/factory:8.5.1@sha256:22c5857b23b94b3fae6e552b928bc0bba33ee9386924ad1bb53621addcd2b59c

# Fail the image build if the requested browser was not installed for this architecture.
RUN chrome --version

WORKDIR /app

COPY . /app

COPY ./docker/entrypoint.sh /

RUN set -eux; \
    for i in 1 2 3 4 5; do \
        npm ci && break; \
        if [ "$i" -eq 5 ]; then \
            exit 1; \
        fi; \
        sleep_time=$((i * 5)); \
        echo "npm ci failed (attempt ${i}/5), retrying in ${sleep_time}s..."; \
        sleep "$sleep_time"; \
    done

ENV TYPE=regression
ENV COMMAND=run

ENTRYPOINT ["/entrypoint.sh"]
