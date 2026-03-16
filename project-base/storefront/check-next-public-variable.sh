#!/bin/sh

grep -rl --exclude=check-next-public-variable.sh --exclude-dir=.next --exclude-dir=node_modules --exclude-dir=.pnpm-store NEXT_PUBLIC_ .

if [[ "$?" == 0 ]]; then
    1>&2 echo "Use of NEXT_PUBLIC_* variable is forbidden because they can't be set during app start"
    1>&2 echo "Use buildPublicConfig() in buildPublicEnvConfig.ts and window.__ENV injection instead"
    exit 1
fi
