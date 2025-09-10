#!/bin/sh
echo "TYPE variable is set to: $TYPE"
echo "COMMAND variable is set to: $COMMAND"
echo "SPEC variable is set to: $SPEC"

if [ -n "$SPEC" ]; then
    echo "🎯 Running specific test: $SPEC"
    if [ "$TYPE" = "regression" ]; then
        echo "🔍 Running npm run specific-regression $SPEC"
        npm run specific-regression "$SPEC"
    else
        echo "🔍 Running npm run specific-base $SPEC"
        npm run specific-base "$SPEC"
    fi
elif [ "$COMMAND" = "generate" ]; then
    echo "📊 Generating snapshots lookup table"
    npm run generate-snapshots-table
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
