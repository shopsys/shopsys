#!/usr/bin/env python3
"""PreToolUse hook for the nightly-triage CI agent: exact-form gate for git push.

The allowlist rule `Bash(git push -u origin nightly-autofix/*)` must use a glob to
cover the date-suffixed branch name, but a trailing glob also matches extra
arguments (e.g. a second refspec `evil:20.0`). This hook closes that hole: any
command starting with `git push` must match the one allowed form exactly.
"""
import json
import re
import sys

command = json.load(sys.stdin).get("tool_input", {}).get("command", "").strip()

if not re.match(r"git\s+push", command):
    sys.exit(0)

if re.fullmatch(r"git push (-u|--set-upstream) origin nightly-autofix/\d{4}-\d{2}-\d{2}", command):
    sys.exit(0)

print(
    "git push blocked by the nightly-triage push guard: the only allowed form is "
    "`git push -u origin nightly-autofix/<YYYY-MM-DD>` — no extra refspecs, flags, or redirects.",
    file=sys.stderr,
)
sys.exit(2)
