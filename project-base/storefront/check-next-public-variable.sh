#!/bin/sh

grep -rl --exclude=check-next-public-variable.sh --exclude-dir=.next --exclude-dir=node_modules NEXT_PUBLIC_ .

if [[ "$?" == 0 ]]; then
    1>&2 echo "Use of NEXT_PUBLIC_* variable is forbidden because they can't be set during app start"
    1>&2 echo "Use publicRuntimeConfig in next.config.js instead"
    1>&2 echo " -- see more https://nextjs.org/docs/basic-features/environment-variables#exposing-environment-variables-to-the-browser"
    exit 1
fi
