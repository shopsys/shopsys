#!/bin/bash

if [ -f "../../parameters_monorepo.yaml" ]; then
    echo 'Installing composer in monorepo project-base is not supported.'
    exit 1
fi

if [ -h "composer.json" ]; then
    echo 'Please run composer install in the app/ subfolder.'
    exit 1
fi
