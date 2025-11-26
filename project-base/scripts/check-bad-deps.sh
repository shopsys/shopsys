#!/usr/bin/env bash

# Script to check for potentially malicious npm packages related to supply chain attacks
# See: https://about.gitlab.com/blog/gitlab-discovers-widespread-npm-supply-chain-attack/

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
BAD_DEPS_FILE="$SCRIPT_DIR/bad-deps.txt"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Spinner characters
SPINNER_CHARS='⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏'
SPINNER_PID=""

# Start spinner in background
start_spinner() {
    local msg="$1"
    (
        i=0
        while true; do
            printf "\r${CYAN}${SPINNER_CHARS:i++%${#SPINNER_CHARS}:1}${NC} %s" "$msg"
            sleep 0.1
        done
    ) &
    SPINNER_PID=$!
    disown
}

# Stop spinner and clear line
stop_spinner() {
    if [[ -n "$SPINNER_PID" ]]; then
        kill "$SPINNER_PID" 2>/dev/null
        wait "$SPINNER_PID" 2>/dev/null
        SPINNER_PID=""
    fi
    printf "\r\033[K"  # Clear the line
}

# Cleanup on exit
cleanup() {
    stop_spinner
}
trap cleanup EXIT

if [[ ! -f "$BAD_DEPS_FILE" ]]; then
    echo -e "${RED}Error: bad-deps.txt not found at $BAD_DEPS_FILE${NC}"
    exit 1
fi

# Count total packages to check
TOTAL_DEPS=$(grep -cv '^$\|^#' "$BAD_DEPS_FILE" 2>/dev/null || echo "0")

echo ""
echo -e "${BOLD}════════════════════════════════════════════════${NC}"
echo -e "${BOLD}  🔒 Supply Chain Attack Dependency Checker${NC}"
echo -e "${BOLD}════════════════════════════════════════════════${NC}"
echo -e "  Checking for malicious packages from ${RED}Shai-Hulud${NC} campaign"
echo -e "  Packages to check: ${CYAN}$TOTAL_DEPS${NC}"
echo -e "${BOLD}════════════════════════════════════════════════${NC}"
echo ""

FOUND_BAD=0
PROJECTS_CHECKED=0

# Function to check a project directory
check_project() {
    local project_dir="$1"
    local package_manager="$2"
    local display_name="$3"

    if [[ ! -d "$project_dir/node_modules" ]]; then
        echo -e "${YELLOW}⏭  Skipping ${BOLD}$display_name${NC}${YELLOW} (no node_modules)${NC}"
        return 0
    fi

    echo -e "${CYAN}📦 Checking ${BOLD}$display_name${NC}${CYAN} using $package_manager...${NC}"
    ((PROJECTS_CHECKED++))

    # Get the full dependency list with spinner
    local full_list
    start_spinner "Fetching dependency tree..."

    if [[ "$package_manager" == "pnpm" ]]; then
        full_list=$(cd "$project_dir" && pnpm list --depth=Infinity 2>/dev/null || echo "")
    else
        full_list=$(cd "$project_dir" && npm list --all 2>/dev/null || echo "")
    fi

    stop_spinner

    if [[ -z "$full_list" ]]; then
        echo -e "   ${YELLOW}⚠  Could not get dependency list${NC}"
        return 0
    fi

    # Check each bad dependency with progress
    local checked=0
    local last_percent=-1

    start_spinner "Scanning for malicious packages..."

    while IFS= read -r dep; do
        # Skip empty lines and comments
        [[ -z "$dep" || "$dep" =~ ^# ]] && continue

        ((checked++))

        # Update progress every 10 packages
        if (( checked % 10 == 0 )); then
            local percent=$((checked * 100 / TOTAL_DEPS))
            if (( percent != last_percent )); then
                stop_spinner
                start_spinner "Scanning packages... ${checked}/${TOTAL_DEPS} (${percent}%)"
                last_percent=$percent
            fi
        fi

        # Use word boundary matching to avoid partial matches
        if echo "$full_list" | grep -qE "(^|[^a-zA-Z0-9@/_-])${dep}(@|[[:space:]]|$)"; then
            stop_spinner
            echo -e "   ${RED}⚠️  FOUND POTENTIALLY MALICIOUS PACKAGE: ${BOLD}$dep${NC}"
            if [[ "$package_manager" == "pnpm" ]]; then
                (cd "$project_dir" && pnpm why "$dep" 2>/dev/null) || true
            else
                (cd "$project_dir" && npm why "$dep" 2>/dev/null) || true
            fi
            start_spinner "Continuing scan..."
            FOUND_BAD=1
        fi
    done < "$BAD_DEPS_FILE"

    stop_spinner
    echo -e "   ${GREEN}✓${NC} Scanned ${CYAN}$checked${NC} packages"
}

# Discover and check all node_modules in the workspace
echo -e "${CYAN}🔍 Discovering node_modules directories...${NC}"
echo ""

# Root workspace (uses npm)
if [[ -f "$REPO_ROOT/package.json" ]]; then
    check_project "$REPO_ROOT" "npm" "root workspace"
    echo ""
fi

# Project-base storefront (uses pnpm)
STOREFRONT_DIR="$REPO_ROOT/project-base/storefront"
if [[ -f "$STOREFRONT_DIR/package.json" ]]; then
    check_project "$STOREFRONT_DIR" "pnpm" "project-base/storefront"
    echo ""
fi

# Storefront Cypress (uses npm - has its own package-lock.json)
CYPRESS_DIR="$REPO_ROOT/project-base/storefront/cypress"
if [[ -f "$CYPRESS_DIR/package.json" ]]; then
    check_project "$CYPRESS_DIR" "npm" "storefront/cypress"
    echo ""
fi

# Project-base app (uses npm)
APP_DIR="$REPO_ROOT/project-base/app"
if [[ -f "$APP_DIR/package.json" ]]; then
    check_project "$APP_DIR" "npm" "project-base/app"
    echo ""
fi

# Framework assets (uses npm)
FRAMEWORK_ASSETS_DIR="$REPO_ROOT/packages/framework/assets"
if [[ -f "$FRAMEWORK_ASSETS_DIR/package.json" ]]; then
    check_project "$FRAMEWORK_ASSETS_DIR" "npm" "packages/framework/assets"
    echo ""
fi

echo -e "${BOLD}════════════════════════════════════════════════${NC}"
echo -e "  Projects checked: ${CYAN}$PROJECTS_CHECKED${NC}"
echo -e "${BOLD}════════════════════════════════════════════════${NC}"

if [[ $FOUND_BAD -eq 1 ]]; then
    echo -e "${RED}  ⚠️  WARNING: Potentially malicious packages found!${NC}"
    echo -e "  Please review the packages above and take immediate action."
    echo -e "  ${CYAN}https://about.gitlab.com/blog/gitlab-discovers-widespread-npm-supply-chain-attack/${NC}"
    echo -e "${BOLD}════════════════════════════════════════════════${NC}"
    exit 1
else
    echo -e "${GREEN}  ✅ No known malicious packages found.${NC}"
    echo -e "  Your dependencies appear safe from the Shai-Hulud campaign."
    echo -e "${BOLD}════════════════════════════════════════════════${NC}"
fi
echo ""
