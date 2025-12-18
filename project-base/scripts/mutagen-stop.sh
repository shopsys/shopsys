#!/bin/bash
set -e

GREEN='\033[0;32m'
YELLOW='\033[0;33m'
NC='\033[0m'

echo -e "${YELLOW}Stopping containers...${NC}"
docker compose stop

echo -e "${YELLOW}Stopping mutagen sync...${NC}"
mutagen project terminate 2>/dev/null || true

echo -e "${YELLOW}Stopping Mutagen sidecar containers...${NC}"
docker compose stop mutagen-php-fpm mutagen-storefront

echo -e "${GREEN}Done${NC}"
