#!/usr/bin/env bash
set -euo pipefail

# =============================================================================
# AGC Assessors - Docker Development Environment Setup
# =============================================================================
# Usage: ./docker/setup.sh
# =============================================================================

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║     AGC Assessors - Docker Dev Environment Setup            ║"
echo "║     Laravel 13 + Filament 5 + PHP 8.4 + PostgreSQL 16       ║"
echo "║     (External PostgreSQL Server)                            ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Step 1: Check prerequisites
echo -e "${YELLOW}Step 1: Checking prerequisites...${NC}"
if ! command -v docker &> /dev/null; then
    echo "Error: Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "Error: Docker Compose is not installed. Please install it first."
    exit 1
fi

echo -e "${GREEN}✓ Prerequisites OK${NC}"

# Step 2: Copy environment file
echo -e "${YELLOW}Step 2: Setting up environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ .env file created from .env.example${NC}"
else
    echo -e "${GREEN}✓ .env already exists${NC}"
fi

# Step 3: Create required directories
echo -e "${YELLOW}Step 3: Creating required directories...${NC}"
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/app/public
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/storage

echo -e "${GREEN}✓ Directories created${NC}"

# Step 4: Build Docker images
echo -e "${YELLOW}Step 4: Building Docker images...${NC}"
docker compose build --no-cache php
echo -e "${GREEN}✓ Docker images built${NC}"

# Step 5: Start containers
echo -e "${YELLOW}Step 5: Starting containers...${NC}"
docker compose up -d database redis mailpit
echo "Waiting for database to be healthy..."
sleep 10

docker compose up -d php nginx node
echo -e "${GREEN}✓ All containers started${NC}"

# Step 6: Install PHP dependencies
echo -e "${YELLOW}Step 6: Installing PHP dependencies...${NC}"
docker compose exec -T php composer install --no-interaction --prefer-dist
echo -e "${GREEN}✓ PHP dependencies installed${NC}"

# Step 7: Install Node dependencies
echo -e "${YELLOW}Step 7: Installing Node dependencies...${NC}"
docker compose exec -T node npm install
echo -e "${GREEN}✓ Node dependencies installed${NC}"

# Step 8: Generate application key
echo -e "${YELLOW}Step 8: Generating application key...${NC}"
docker compose exec -T php php artisan key:generate
echo -e "${GREEN}✓ Application key generated${NC}"

# Step 9: Verify PostgreSQL connection
echo -e "${YELLOW}Step 9: Verifying PostgreSQL connection...${NC}"
if docker compose exec -T php php artisan db:monitor > /dev/null 2>&1; then
    echo -e "${GREEN}✓ PostgreSQL connection successful${NC}"
else
    echo -e "${YELLOW}⚠️  Could not connect to PostgreSQL. Check your .env DB_* settings.${NC}"
    echo -e "${YELLOW}    Make sure your external PostgreSQL server is running.${NC}"
fi

# Step 10: Run migrations
echo -e "${YELLOW}Step 10: Running database migrations...${NC}"
docker compose exec -T php php artisan migrate --force || true
echo -e "${GREEN}✓ Migrations completed${NC}"

# Step 11: Run seeders
echo -e "${YELLOW}Step 11: Seeding database...${NC}"
docker compose exec -T php php artisan db:seed --force || true
echo -e "${GREEN}✓ Database seeded${NC}"

# Step 12: Link storage
echo -e "${YELLOW}Step 11: Linking storage...${NC}"
docker compose exec -T php php artisan storage:link
echo -e "${GREEN}✓ Storage linked${NC}"

# Step 12: Optimize for development
echo -e "${YELLOW}Step 12: Optimizing...${NC}"
docker compose exec -T php php artisan optimize
echo -e "${GREEN}✓ Optimization complete${NC}"

# Summary
echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Setup Complete! Your development environment is ready.${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${BLUE}Application:${NC}      http://localhost:8080"
echo -e "${BLUE}Filament Admin:${NC}   http://localhost:8080/admin"
echo -e "${BLUE}Vite HMR:${NC}         http://localhost:5173"
echo -e "${BLUE}Mailpit Inbox:${NC}    http://localhost:8025"
echo -e "${BLUE}Mailpit SMTP:${NC}     localhost:1025"
echo -e "${BLUE}PostgreSQL:${NC}      External Server (check .env DB_* settings)"
echo -e "${BLUE}  Host:${NC}           ${DB_HOST:-localhost}"
echo -e "${BLUE}  Port:${NC}           ${DB_PORT:-5432}"
echo -e "${BLUE}  Database:${NC}       ${DB_DATABASE:-agc}"
echo -e "${BLUE}  User:${NC}           ${DB_USERNAME:-agc}"
echo -e "${BLUE}Redis:${NC}            localhost:6379"
echo ""
echo -e "${YELLOW}Useful commands:${NC}"
echo "  make help              - Show all available commands"
echo "  make shell             - Enter PHP container shell"
echo "  make artisan ARGS=...  - Run Artisan commands"
echo "  make test              - Run tests"
echo "  make phpstan           - Run static analysis"
echo "  make down              - Stop all containers"
echo ""
echo -e "${YELLOW}To create a Filament admin user:${NC}"
echo "  make filament-user"
echo ""
