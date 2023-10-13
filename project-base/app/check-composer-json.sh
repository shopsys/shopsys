#!/bin/bash

if [ -h "composer.json" ]; then
    echo 'Please run composer install in the app/ subfolder.'
    exit 1
fi
