#!/bin/sh

echo "TYPE variable is set to: $TYPE"

if [ "$TYPE" = "actual" ]; then
    npm run actual
else
    npm run base
fi
