#!/usr/bin/env bash
set -euo pipefail

npm_name_license_tsv() {
    npm exec --yes license-checker -- --json 2> /dev/null |
        node -e '
            let data = "";
            process.stdin.setEncoding("utf8");
            process.stdin.on("data", c => data += c);
            process.stdin.on("end", () => {
                try {
                    const obj = JSON.parse(data || "{}");
                    for (const [key, val] of Object.entries(obj)) {
                        const licenses = val.licenses ?? val.license;
                        if (!licenses) continue;
                        const name = val.name ?? key.replace(/@[^@]+$/, "");
                        const lic = Array.isArray(licenses) ? licenses.join(" | ") : String(licenses);
                        process.stdout.write(`${name}\t${lic}\n`);
                    }
                } catch (e) {}
            });
        '
}

composer_name_license_tsv() {
    composer licenses --format=json 2> /dev/null |
        node -e '
            let data = "";
            process.stdin.setEncoding("utf8");
            process.stdin.on("data", c => data += c);
            process.stdin.on("end", () => {
                try {
                    const json = JSON.parse(data || "{}");
                    const deps = json.dependencies || {};
                    for (const [pkg, val] of Object.entries(deps)) {
                        const licenses = (val.license ?? val.licenses) ?? [];
                        const lic = Array.isArray(licenses) ? licenses.join(" | ") : String(licenses);
                        process.stdout.write(`${pkg}\t${lic}\n`);
                    }
                } catch (e) {}
            });
        '
}

# Basic permissive licenses always allowed
BASIC_LICENSES="0BSD,Apache-2.0,Artistic-1.0,BSD*,BSD-2-Clause,BSD-3-Clause,CC-BY-3.0,CC-BY-4.0,CC0-1.0,ISC,MIT,MIT*,Python-2.0,UNLICENSED,WTFPL"
# GPL licenses for project builds (not for framework)
GPL_LICENSES="GPL-2.0-only,GPL-3.0-only,LGPL-2.1-only,LGPL-3.0"

get_allowed_licenses() {
    # Use ALLOWED if explicitly set
    if [ -n "${ALLOWED:-}" ]; then
        echo "$ALLOWED"
        return
    fi

    # For project builds (when .env file exists), include GPL licenses
    if [ -f "app/.env" ] || [ -f ".env" ]; then
        echo "${BASIC_LICENSES},${GPL_LICENSES}"
    else
        # Framework build - only basic licenses
        echo "${BASIC_LICENSES}"
    fi
}

ALLOWED_LIST=$(get_allowed_licenses | tr ',' '\n' | sed 's/^\s*//;s/\s*$//' | awk 'NF')

normalize_license_field() {
    sed -E \
        -e 's/[()\"]//g' \
        -e 's/[[:space:]]*\|[[:space:]]*/|/g' \
        -e 's/[[:space:]]+OR[[:space:]]+/|/Ig' \
        -e 's/[[:space:]]+AND[[:space:]]+/|/Ig' \
        -e 's/[[:space:]]+WITH[[:space:]]+/|/Ig' \
        -e 's/[,/;]+/|/g' \
        -e 's/[[:space:]]+/ /g' \
        -e 's/^\s*//;s/\s*$//' \
        -e 's/\|{2,}/|/g' \
    | tr '|' '\n' \
    | sed 's/^\s*//;s/\s*$//' \
    | awk 'NF'
}

is_allowed() {
    local license="$1"
    license=$(echo "$license" | sed 's/^\s*//;s/\s*$//')
    [ -z "$license" ] && return 1

    if printf '%s\n' "$ALLOWED_LIST" | grep -Fx -- "$license" > /dev/null; then
        return 0
    fi
    return 1
}

package_is_accepted() {
    while IFS= read -r license; do
        if is_allowed "$license"; then
            return 0
        fi
    done
    return 1
}

validate_from_tsv() {
    while IFS=$'\t' read -r name licenses; do
        [ -z "${name:-}" ] && continue
        case "$name" in
            shopsys*|@shopsys*)
                continue
                ;;
        esac
        normalized=$(printf "%s\n" "$licenses" | normalize_license_field)
        [ -z "${normalized:-}" ] && continue

        if ! package_is_accepted <<EOF
$normalized
EOF
        then
            printf "%s (%s)\n" "$name" "$licenses"
        fi
    done
}

composer_violations="$(composer_name_license_tsv | validate_from_tsv)"
npm_violations="$(npm_name_license_tsv | validate_from_tsv)"

exit_code=0

if [ -n "$composer_violations" ]; then
    echo ""
    echo "❌ Composer packages with non-approved licenses (review BASIC_LICENSES and GPL_LICENSES variables in both check-licenses.sh files if you want to allow it):"
    printf "%s\n" "$composer_violations" | sort -u | sed 's/^/  - /'
    echo ""
    exit_code=1
else
    echo "✅ Composer: All dependencies use approved licenses"
fi

if [ -n "$npm_violations" ]; then
    echo ""
    echo "❌ NPM packages with non-approved licenses (review BASIC_LICENSES and GPL_LICENSES variables in both check-licenses.sh files if you want to allow it):"
    printf "%s\n" "$npm_violations" | sort -u | sed 's/^/  - /'
    echo ""
    exit_code=1
else
    echo "✅ NPM: All dependencies use approved licenses"
fi

exit $exit_code
