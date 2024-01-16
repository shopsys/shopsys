#!/bin/sh

TIME_LIMIT=${1:-60}

php ./bin/console messenger:consume \
    product_recalculation_priority_high \
    product_recalculation_priority_regular \
    placed_order_transport \
    --time-limit=$TIME_LIMIT
