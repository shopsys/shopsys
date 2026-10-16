#!/bin/sh
set -e

TYPE=${TYPE:-regression}
COMMAND=${COMMAND:-run}
SKIP_SNAPSHOTS=${SKIP_SNAPSHOTS-0}
export TYPE COMMAND SKIP_SNAPSHOTS

case "$COMMAND" in
    run|selected|open|smoke) ;;
    *) echo "Unknown Cypress COMMAND: $COMMAND" >&2; exit 1 ;;
esac
case "$TYPE" in
    regression|base) ;;
    *) echo "Unknown Cypress TYPE: $TYPE (expected regression or base)" >&2; exit 1 ;;
esac
case "$SKIP_SNAPSHOTS:$TYPE" in
    0:*) ;;
    1:regression) ;;
    1:base) echo "Cannot generate screenshot references with SKIP_SNAPSHOTS=1." >&2; exit 1 ;;
    *) echo "SKIP_SNAPSHOTS must be 0 or 1." >&2; exit 1 ;;
esac

# Install dependencies if node_modules is empty or cypress is missing
if [ ! -d "/app/node_modules/cypress" ]; then
    echo "📦 Installing dependencies..."
    npm ci
fi

echo "TYPE variable is set to: $TYPE"
echo "COMMAND variable is set to: $COMMAND"
echo "SPEC variable is set to: $SPEC"
echo "SKIP_SNAPSHOTS variable is set to: $SKIP_SNAPSHOTS"

if [ -n "$SPEC" ]; then
    echo "🎯 Running specific test: $SPEC"
    if [ "$TYPE" = "regression" ]; then
        echo "🔍 Running npm run specific-regression $SPEC"
        npm run specific-regression "$SPEC"
    else
        echo "🔍 Running npm run specific-base $SPEC"
        npm run specific-base "$SPEC"
    fi
elif [ "$COMMAND" = "smoke" ]; then
    echo "💨 Running smoke tests"
    npm run smoke
elif [ "$COMMAND" = "open" ]; then
    echo "🖥️  DISPLAY variable is set to: $DISPLAY"
    if [ "$TYPE" = "regression" ]; then
        npm run open-regression
    else
        npm run open-base
    fi
elif [ "$COMMAND" = "selected" ]; then
    echo "📋 Running selected tests"
    if [ "$TYPE" = "regression" ]; then
        npm run selected-regression
    else
        npm run selected-base
    fi
else
    echo "🚀 Running default tests"
    if [ "$TYPE" = "regression" ]; then
        npm run regression
    else
        npm run base
    fi
fi
