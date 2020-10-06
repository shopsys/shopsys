#!/bin/bash
# https://docs.docker.com/compose/startup-order/
set -e

while ! nc -z rabbitmq 5672; do sleep 1; done


echo "Rabbit MQ is up, run a consumer"
