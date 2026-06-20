#!/bin/sh

set -e

install_with_retry() {
  for i in 1 2 3 4 5; do
    # pnpm 10 asks for confirmation before purging node_modules; detached containers have no TTY.
    if CI=true pnpm install "$@"; then
      return 0
    fi

    if [ "$i" -eq 5 ]; then
      return 1
    fi

    sleep_time=$((i * 5))
    echo "pnpm install failed (attempt $i/5). Retrying in ${sleep_time}s..."
    sleep "$sleep_time"
  done
}

# The run command is taken from the STOREFRONT_RUN_COMMAND env variable (set in docker-compose.yml
# and toggled by `make environment-dev` / `make environment-prod`), falling back to the container
# command argument and then to `dev`.
RUN_COMMAND="${STOREFRONT_RUN_COMMAND:-${1:-dev}}"

case "$RUN_COMMAND" in
  "dev")
    install_with_retry
    exec pnpm run dev ;;
  "build")
    install_with_retry --frozen-lockfile
    pnpm run build
    exec pnpm run start ;;
  *)
    exec "$@"
esac
