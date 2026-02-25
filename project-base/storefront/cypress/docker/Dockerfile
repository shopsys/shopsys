FROM cypress/base:24.14.0

WORKDIR /app

COPY . /app

COPY ./docker/entrypoint.sh /

RUN set -eux; \
    for i in 1 2 3 4 5; do \
        npm ci --force && break; \
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
