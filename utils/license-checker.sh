#!/bin/bash

ALLOWLIST_LICENSES=("MIT" "Apache-2.0" "BSD-2-Clause" "BSD-3-Clause" "WTFPL" "Artistic-1.0")

QUIET=false
if [ "$1" = "--quiet" ] || [ "$1" = "-q" ]; then
    QUIET=true
fi

if [ "$QUIET" = false ]; then
    echo "=== Composer License Checker for All Packages ==="
    echo ""
fi

ROOT_DIR=$(pwd)

get_license_checker_command() {
    if ! command -v composer >/dev/null 2>&1; then
        echo "❌ Composer not found. Please install composer first." >&2
        return 1
    fi

    COMPOSER_HOME=$(composer config -g home 2>/dev/null)

    if [ -n "$COMPOSER_HOME" ] && [ -f "$COMPOSER_HOME/vendor/bin/composer-license-checker" ]; then
        echo "$COMPOSER_HOME/vendor/bin/composer-license-checker"
        return 0
    fi

    # Try to install globally
    if [ "$QUIET" = false ]; then
        echo "🔧 Composer license checker not found, installing globally..." >&2
    fi

    composer global require dominikb/composer-license-checker --no-interaction  > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        echo "❌ Failed to install composer-license-checker" >&2
        return 1
    fi

    if [ "$QUIET" = false ]; then
        echo "✅ Composer license checker installed globally" >&2
        echo "" >&2
    fi

    if [ -n "$COMPOSER_HOME" ] && [ -f "$COMPOSER_HOME/vendor/bin/composer-license-checker" ]; then
        echo "$COMPOSER_HOME/vendor/bin/composer-license-checker"
        return 0
    fi

    echo "❌ composer-license-checker not found in composer global directory" >&2
    return 1
}

# Get license checker command
if ! LICENSE_CHECKER_CMD=$(get_license_checker_command); then
    echo "❌ Failed to get license checker command"
    exit 1
fi

# Build license command once
LICENSE_CMD="check"
for license in "${ALLOWLIST_LICENSES[@]}"; do
    LICENSE_CMD="$LICENSE_CMD --allowlist $license"
done
LICENSE_CMD="$LICENSE_CMD --allow shopsys"

check_licenses() {
    local dir="$1"
    local relative_path="$dir"

    if [ "$QUIET" = false ]; then
        echo "📦 Checking: $relative_path"
    fi

    cd "$dir" || return 1

    # Check if composer.json exists
    if [ ! -f "composer.json" ]; then
        echo "❌ No composer.json found in $relative_path"
        cd - > /dev/null
        return 1
    fi

    # Always install dependencies
    if [ "$QUIET" = false ]; then
        echo "🔧 Installing dependencies for $relative_path..."
    fi

    SKIP_COMPOSER_CHECK=true composer install --no-interaction --no-scripts > /dev/null 2>&1

    if [ $? -ne 0 ]; then
        echo "❌ Failed to install dependencies for $relative_path"
        cd - > /dev/null
        return 1
    fi

    # Run license check
    if [ "$QUIET" = true ]; then
        output=$($LICENSE_CHECKER_CMD $LICENSE_CMD 2>&1)
        exit_code=$?

        # Clean up vendor directory and composer.lock for subdirectories (not root)
        if [ "$relative_path" != "." ]; then
            rm -rf $relative_path/vendor composer.lock
            if [ "$QUIET" = false ]; then
                echo "🧹 Cleaned up $relative_path dependencies"
            fi
        fi

        if [ $exit_code -ne 0 ]; then
            echo "❌ License check failed for $relative_path"
            echo "$output"
            echo ""
            cd - > /dev/null
            return 1
        fi
    else
        if ! $LICENSE_CHECKER_CMD $LICENSE_CMD; then
            cd - > /dev/null
            return 1
        fi
    fi

    cd - > /dev/null
    if [ "$QUIET" = false ]; then
        echo ""
    fi
    return 0
}

DIRS_TO_CHECK=("." "project-base/app")

for package_dir in packages/*/; do
    if [ -d "$package_dir" ] && [ -f "${package_dir}composer.json" ]; then
        DIRS_TO_CHECK+=("$package_dir")
    fi
done

OVERALL_EXIT_CODE=0

for dir in "${DIRS_TO_CHECK[@]}"; do
    if ! check_licenses "$dir"; then
        OVERALL_EXIT_CODE=1
    fi
done

if [ $OVERALL_EXIT_CODE -eq 0 ]; then
    echo "🎉 All license checks completed successfully!"
else
    echo "❌ Some license checks failed!"
fi

exit $OVERALL_EXIT_CODE