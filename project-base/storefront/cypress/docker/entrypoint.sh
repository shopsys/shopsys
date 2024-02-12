#!/bin/sh

echo "TYPE variable is set to: $TYPE"
chmod -R 755 /app
npm ci --force

if [ "$TYPE" = "actual" ]; then
    npm run actual
else
    npm run base
fi
