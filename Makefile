# =============================================================================
# AGC Assessors - Makefile for Docker Development
# =============================================================================
# Stack: Laravel 13 + Filament 5 + PHP 8.4 + Node 24 + PostgreSQL 16
# Run `make help` to see all available commands
# =============================================================================

.PHONY: help build up down restart shell composer artisan npm test phpstan fresh install migrate seed clear logs

# Default command
.DEFAULT_GOAL := help

# Colors
GREEN := \033[0;32m
YELLOW := \033[0;33m
BLUE := \033[0;34m
NC := \033[0m # No Color

help: ## Show this help message
	@echo "${GREEN}AGC Assessors - Development Commands${NC}"
	@echo "======================================"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  ${BLUE}%-20s${NC} %s\n", $$1, $$2}'

# =============================================================================
# Docker Lifecycle
# =============================================================================

build: ## Build all Docker images
	@echo "${YELLOW}Building Docker images...${NC}"
	docker compose build --no-cache

up: ## Start all containers in detached mode
	@echo "${YELLOW}Starting containers...${NC}"
	docker compose up -d
	@echo "${GREEN}Done! App: http://localhost:8080 | Mailpit: http://localhost:8025 | Vite: http://localhost:5173${NC}"

down: ## Stop and remove all containers
	@echo "${YELLOW}Stopping containers...${NC}"
	docker compose down

restart: ## Restart all containers
	@echo "${YELLOW}Restarting containers...${NC}"
	docker compose restart

fresh: ## Destroy everything and rebuild from scratch (WARNING: deletes DB data)
	@echo "${YELLOW}Destroying everything...${NC}"
	docker compose down -v --remove-orphans
	@echo "${YELLOW}Rebuilding...${NC}"
	docker compose build --no-cache
	@echo "${YELLOW}Starting fresh...${NC}"
	docker compose up -d
	@echo "${GREEN}Fresh environment ready!${NC}"

# =============================================================================
# Application Setup
# =============================================================================

install: ## Full first-time setup (build, up, composer install, npm install, migrate, seed)
	@echo "${YELLOW}Starting full installation...${NC}"
	@make build
	@make up
	@echo "${YELLOW}Installing PHP dependencies...${NC}"
	@sleep 5
	@make composer-install
	@echo "${YELLOW}Installing Node dependencies...${NC}"
	@make npm-install
	@echo "${YELLOW}Generating APP_KEY...${NC}"
	@make artisan-key
	@echo "${YELLOW}Running migrations...${NC}"
	@make migrate
	@echo "${YELLOW}Seeding database...${NC}"
	@make seed
	@echo "${YELLOW}Linking storage...${NC}"
	@make artisan ARGS="storage:link"
	@echo "${YELLOW}Optimizing...${NC}"
	@make optimize
	@echo "${GREEN}Installation complete!${NC}"
	@echo "${GREEN}App: http://localhost:8080${NC}"
	@echo "${GREEN}Filament 5 Admin: http://localhost:8080/admin${NC}"
	@echo "${GREEN}Mailpit: http://localhost:8025${NC}"

# =============================================================================
# Service Access
# =============================================================================
# Service Access
# =============================================================================

shell: ## Open bash shell in PHP container
	docker compose exec php bash

shell-node: ## Open shell in Node container
	docker compose exec node sh

shell-db: ## Open PostgreSQL console (requires psql on host or use docker exec on external container)
	@echo "${YELLOW}Connect to your external PostgreSQL server:${NC}"
	@echo "psql -h $(DB_HOST) -U $(DB_USERNAME) -d $(DB_DATABASE)"
	@echo "Or use: docker exec -it <your-postgres-container> psql -U $(DB_USERNAME) -d $(DB_DATABASE)"

# =============================================================================
# PHP / Composer Commands
# =============================================================================

composer: ## Run Composer command (e.g., make composer ARGS="require laravel/sanctum")
	docker compose exec php composer $(ARGS)

composer-install: ## Install PHP dependencies
	docker compose exec php composer install

composer-update: ## Update PHP dependencies
	docker compose exec php composer update

composer-dump: ## Dump autoload
	docker compose exec php composer dump-autoload

# =============================================================================
# Artisan Commands
# =============================================================================

artisan: ## Run Artisan command (e.g., make artisan ARGS="migrate:fresh --seed")
	docker compose exec php php artisan $(ARGS)

artisan-key: ## Generate application key
	docker compose exec php php artisan key:generate

migrate: ## Run database migrations
	docker compose exec php php artisan migrate

migrate-fresh: ## Fresh migrate with seed
	docker compose exec php php artisan migrate:fresh --seed

migrate-rollback: ## Rollback last migration batch
	docker compose exec php php artisan migrate:rollback

seed: ## Run database seeders
	docker compose exec php php artisan db:seed

optimize: ## Optimize Laravel for production cache
	docker compose exec php php artisan optimize

optimize-clear: ## Clear all Laravel caches
	docker compose exec php php artisan optimize:clear

filament-optimize: ## Optimize Filament
	docker compose exec php php artisan filament:optimize

filament-user: ## Create Filament admin user
	docker compose exec php php artisan make:filament-user

# =============================================================================
# Node / NPM Commands
# =============================================================================

npm: ## Run npm command (e.g., make npm ARGS="run build")
	docker compose exec node npm $(ARGS)

npm-install: ## Install Node dependencies
	docker compose exec node npm install

npm-update: ## Update Node dependencies
	docker compose exec node npm update

dev: ## Start Vite dev server (HMR)
	docker compose exec node npm run dev

build-assets: ## Build production assets
	docker compose exec node npm run build

# =============================================================================
# Testing & Quality
# =============================================================================

test: ## Run PHPUnit/Pest tests
	docker compose exec php php artisan test

test-coverage: ## Run tests with coverage
	docker compose exec php php artisan test --coverage

phpstan: ## Run PHPStan static analysis
	docker compose exec php vendor/bin/phpstan analyse --memory-limit=1G

phpstan-init: ## Initialize PHPStan
	docker compose exec php vendor/bin/phpstan analyse --generate-baseline

pint: ## Run Laravel Pint (code style)
	docker compose exec php vendor/bin/pint

pint-dry: ## Run Pint in dry-run mode
	docker compose exec php vendor/bin/pint --test

# =============================================================================
# Queue & Scheduler (optional profiles)
# =============================================================================

queue-up: ## Start queue worker container
	docker compose --profile queue up -d queue

queue-logs: ## Show queue worker logs
	docker compose logs -f queue

scheduler-up: ## Start scheduler container
	docker compose --profile scheduler up -d scheduler

# =============================================================================
# Maintenance
# =============================================================================

clear: ## Clear all caches (Laravel + Filament)
	@echo "${YELLOW}Clearing caches...${NC}"
	@docker compose exec php php artisan optimize:clear
	@docker compose exec php php artisan filament:optimize-clear
	@docker compose exec php php artisan icons:clear
	@echo "${GREEN}Caches cleared!${NC}"

logs: ## Show logs for all containers
	docker compose logs -f

logs-php: ## Show PHP container logs
	docker compose logs -f php

logs-nginx: ## Show Nginx container logs
	docker compose logs -f nginx

logs-db: ## Show database container logs (if using local postgres container)
	docker compose logs -f database || echo "${YELLOW}No local database container. Check your external PostgreSQL server.${NC}"

db-export: ## Export database to backup.sql (requires pg_dump on host)
	pg_dump -h $(DB_HOST) -U $(DB_USERNAME) -d $(DB_DATABASE) > backup.sql

db-import: ## Import database from backup.sql (requires psql on host)
	psql -h $(DB_HOST) -U $(DB_USERNAME) -d $(DB_DATABASE) < backup.sql

# =============================================================================
# Media & Storage
# =============================================================================

storage-link: ## Create storage symbolic link
	docker compose exec php php artisan storage:link

media-clear: ## Clear temporary media files
	docker compose exec php php artisan media:clear

# =============================================================================
# Info & Status
# =============================================================================

status: ## Show container status
	@echo "${GREEN}Container Status:${NC}"
	@docker compose ps

ports: ## Show exposed ports
	@echo "${GREEN}Exposed Ports:${NC}"
	@echo "  App (Nginx):     http://localhost:${APP_PORT:-8080}"
	@echo "  Vite (Node):     http://localhost:${VITE_PORT:-5173}"
	@echo "  Mailpit UI:      http://localhost:${MAILPIT_UI_PORT:-8025}"
	@echo "  Mailpit SMTP:    localhost:${MAILPIT_SMTP_PORT:-1025}"
	@echo "  PostgreSQL:      localhost:${DB_PORT:-5432} (EXTERNAL SERVER)"
	@echo "  Redis:           localhost:${REDIS_PORT:-6379}"
