#!/bin/sh

TIME_LIMIT=${1:-60}

PIPE=/tmp/log-pipe
rm -rf $PIPE
mkfifo $PIPE
chmod 666 $PIPE
stdbuf -o0 tail -n +1 -f $PIPE &

sleep 5

while true; do
    php ./bin/console messenger:consume \
        product_recalculation_priority_high \
        product_recalculation_priority_regular \
        placed_order_transport \
        send_email_transport \
        article_export_transport \
        --time-limit=$TIME_LIMIT --quiet
    sleep 2
done

