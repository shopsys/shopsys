#!/bin/sh

set -e

case "$1" in
  "dev")
    npm install
    exec npm run dev ;;
  "build")
    npm ci
    npm run build
    exec npm run start;;
  *)
    exec "$@"
esac
