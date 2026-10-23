.PHONY: help up down build restart bash test migrate seed prod-build prod-up prod-down logs clean

help:
	@echo "Icegozi Novel Platform - Enterprise Docker Commands"
	@echo "  make up          - Start development environment in background"
	@echo "  make down        - Stop development environment"
	@echo "  make build       - Rebuild development Docker images"
	@echo "  make restart     - Restart development stack"
	@echo "  make bash        - Open interactive shell in app container"
	@echo "  make test        - Run PHPUnit test suite inside container"
	@echo "  make migrate     - Run database migrations"
	@echo "  make seed        - Run database seeders"
	@echo "  make prod-build  - Build production Docker image"
	@echo "  make prod-up     - Start production stack"
	@echo "  make prod-down   - Stop production stack"
	@echo "  make logs        - Tail container logs"
	@echo "  make clean       - Remove containers, networks, and untracked volumes"

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

restart:
	docker compose restart

bash:
	docker compose exec app bash

test:
	docker compose exec app php artisan test

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

prod-build:
	docker compose -f docker-compose.prod.yml build

prod-up:
	docker compose -f docker-compose.prod.yml up -d

prod-down:
	docker compose -f docker-compose.prod.yml down

logs:
	docker compose logs -f

clean:
	docker compose down -v --remove-orphans
