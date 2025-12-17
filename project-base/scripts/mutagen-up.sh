#!/bin/bash
set -e

GREEN='\033[0;32m'
YELLOW='\033[0;33m'
NC='\033[0m'

BUILD=false
NO_CACHE=""
for arg in "$@"; do
    case $arg in
        --build) BUILD=true ;;
        --no-cache) NO_CACHE="--no-cache" ;;
    esac
done

if [[ "$BUILD" == true ]]; then
    echo -e "${YELLOW}Building images${NO_CACHE:+ without cache}...${NC}"
    docker compose build $NO_CACHE
fi

echo -e "${YELLOW}Starting Mutagen sidecar containers...${NC}"
docker compose up -d mutagen-php-fpm mutagen-storefront

echo -e "${YELLOW}Cleaning up any existing mutagen sessions...${NC}"
mutagen project terminate 2>/dev/null || true

echo -e "${YELLOW}Starting mutagen sync...${NC}"
mutagen project start

echo -e "${YELLOW}Waiting for initial sync...${NC}"
until mutagen sync list 2>/dev/null | grep -q "Watching"; do
    sleep 2
done

echo -e "${YELLOW}Starting all containers...${NC}"
docker compose up -d --force-recreate

echo -e "${GREEN}Done${NC}"
