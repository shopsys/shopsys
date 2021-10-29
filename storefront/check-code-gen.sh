#!/bin/sh

GENERATED_FILENAME=./graphql/generated/index.tsx
ORIGINAL_HASH=$(md5sum $GENERATED_FILENAME | awk '{ print $1 }')

npm run gql

NEW_HASH=$(md5sum $GENERATED_FILENAME | awk '{ print $1 }')


if [[ $ORIGINAL_HASH != $NEW_HASH ]]
then
    echo "File name $GENERATED_FILENAME needs to be generated. Use \"npm run gql\" and commit changes."
    exit 1
fi
