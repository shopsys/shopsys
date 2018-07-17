#!/usr/bin/env bash

# Backup tag refs, because `git filter-branch` doesn't do it
# Backup into refs/original-tags/ because `git filter-branch` needs /refs/original/ empty
#
# Usage: tag_refs_backup.sh

for TAG_REF in $(git for-each-ref --format="%(refname)" refs/tags/); do
    git update-ref refs/original-tags/$TAG_REF $TAG_REF
done
