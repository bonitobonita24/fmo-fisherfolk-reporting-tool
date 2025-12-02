#!/bin/bash

# Fisherfolk Dashboard - Development Server Runner

# Set development environment
export APP_ENV=development

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

SERVER_HOST="localhost"
SERVER_PORT="8080"

echo ""
echo -e "${BLUE}╔═══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     Fisherfolk Dashboard - Development Server                ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}🚀 Starting PHP development server...${NC}"
echo ""
echo -e "   📍 URL: ${YELLOW}http://$SERVER_HOST:$SERVER_PORT${NC}"
echo -e "   📂 Root: $(pwd)/public"
echo ""
echo -e "${GREEN}Press Ctrl+C to stop the server${NC}"
echo ""
echo "─────────────────────────────────────────────────────────────"
echo ""

php -S $SERVER_HOST:$SERVER_PORT -t public dev-router.php
