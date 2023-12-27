#!/bin/bash -e

RABBITMQCTL_EXEC='docker exec ci_rabbitmq_1 rabbitmqctl'
VHOST=$2

if [[ "$VHOST" == "" ]]; then
    echo "Usage: $0 <create/remove> <vhost>"
    exit 1
fi

create () {
    $RABBITMQCTL_EXEC add_vhost "${VHOST}"
    $RABBITMQCTL_EXEC set_permissions -p "${VHOST}" guest ".*" ".*" ".*"
}

remove() {
    $RABBITMQCTL_EXEC delete_vhost "${VHOST}" || true
}

case $1 in
    create) "$@"; exit;;
    remove) "$@"; exit;;
    *) echo "Usage: $0 <create/remove> <vhost>"; exit 1;;
esac
