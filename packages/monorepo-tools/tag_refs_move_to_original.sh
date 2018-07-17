#!/usr/bin/env bash

# Move tag refs from refs/original-tags/ into refs/original/
#
# Usage: tag_refs_move_to_original.sh

for TAG_REF in $(git for-each-ref --format="%(refname)" refs/original-tags/); do
    git update-ref refs/original/"${TAG_REF#refs/original-tags/}" $TAG_REF
    git update-ref -d $TAG_REF
done
