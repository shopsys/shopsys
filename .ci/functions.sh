#!/bin/bash -e

function assertVariable() {
    var="$1"
    if [ -n "${!var}" ]; then
        return
    else
        echo "Variable $1 is not set"
        return 2
    fi
}
