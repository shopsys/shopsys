#!/bin/sh

TIME_LIMIT=${1:-60}

sleep 5

while true; do
    php ./bin/console messenger:consume \
        product_recalculation_priority_high \
        product_recalculation_priority_regular \
        placed_order_transport \
        send_email_transport \
        --time-limit=$TIME_LIMIT
    sleep 2
done

