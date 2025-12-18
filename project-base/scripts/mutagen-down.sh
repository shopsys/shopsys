#!/bin/bash
set -e

GREEN='\033[0;32m'
YELLOW='\033[0;33m'
NC='\033[0m'

echo -e "${YELLOW}Removing containers...${NC}"
docker compose down

echo -e "${YELLOW}Stopping mutagen sync...${NC}"
mutagen project terminate 2>/dev/null || true

echo -e "${YELLOW}Removing Mutagen sidecar containers...${NC}"
docker compose down mutagen-php-fpm mutagen-storefront

echo -e "${GREEN}Done${NC}"
