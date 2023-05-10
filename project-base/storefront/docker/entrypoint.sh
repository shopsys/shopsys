#!/bin/sh

set -e

case "$1" in
  "dev")
    pnpm install
    exec pnpm run dev ;;
  "build")
    pnpm install --frozen-lockfile
    pnpm run build
    exec pnpm run start ;;
  *)
    exec "$@"
esac
