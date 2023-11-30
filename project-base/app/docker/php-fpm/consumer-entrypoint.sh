#!/bin/sh

TIME_LIMIT=${1:-60}

php ./bin/console messenger:consume \
    example_transport \
    placed_order_transport \
    --time-limit=$TIME_LIMIT
