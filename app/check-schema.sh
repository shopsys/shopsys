#!/bin/bash -e

GENERATED_FILENAME=./schema.graphql
ORIGINAL_HASH=$(md5sum $GENERATED_FILENAME | awk '{ print $1 }')

php phing frontend-api-generate-graphql-schema

NEW_HASH=$(md5sum $GENERATED_FILENAME | awk '{ print $1 }')

if [[ $ORIGINAL_HASH != $NEW_HASH ]]
then
    echo "File name $GENERATED_FILENAME needs to be generated. Use \"php phing frontend-api-generate-graphql-schema\" and commit changes."
    exit 1
fi
