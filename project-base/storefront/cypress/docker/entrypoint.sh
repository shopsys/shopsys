#!/bin/sh
echo "TYPE variable is set to: $TYPE"
echo "COMMAND variable is set to: $COMMAND"

if [ "$COMMAND" = "open" ]; then
    echo "DISPLAY variable is set to: $DISPLAY"
    if [ "$TYPE" = "actual" ]; then
        npm run open-actual
    else
        npm run open-base
    fi
elif [ "$COMMAND" = "selected" ]; then
    if [ "$TYPE" = "actual" ]; then
        npm run selected-actual
    else
        npm run selected-base
    fi
else
    if [ "$TYPE" = "actual" ]; then
        npm run actual
    else
        npm run base
    fi
fi
