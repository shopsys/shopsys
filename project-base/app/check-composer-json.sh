#!/bin/bash

if [ -f "../../parameters_monorepo.yaml" ]; then
    echo 'Installing composer in the project-base subfolder in the monorepo is not supported. Install composer in the monorepo root instead.'
    exit 1
fi

if [ -h "composer.json" ]; then
    echo 'Please run composer install in the app/ subfolder.'
    exit 1
fi
