param (
    [Parameter(Position = 0)]
    [string]$Command = "help",
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$RemainingArgs
)

switch ($Command.ToLower()) {
    "up" {
        docker compose up -d
    }
    "down" {
        docker compose down
    }
    "build" {
        docker compose build
    }
    "restart" {
        docker compose restart
    }
    "bash" {
        docker compose exec app bash
    }
    "test" {
        docker compose exec app php artisan test $RemainingArgs
    }
    "migrate" {
        docker compose exec app php artisan migrate $RemainingArgs
    }
    "seed" {
        docker compose exec app php artisan db:seed $RemainingArgs
    }
    "artisan" {
        docker compose exec app php artisan $RemainingArgs
    }
    "logs" {
        docker compose logs -f $RemainingArgs
    }
    "prod-build" {
        docker compose -f docker-compose.prod.yml build
    }
    "prod-up" {
        docker compose -f docker-compose.prod.yml up -d
    }
    "prod-down" {
        docker compose -f docker-compose.prod.yml down
    }
    "clean" {
        docker compose down -v --remove-orphans
    }
    default {
        Write-Host "Icegozi Novel Platform - Windows Docker Helper (./dev.ps1)" -ForegroundColor Cyan
        Write-Host "  ./dev.ps1 up          - Start development environment in background"
        Write-Host "  ./dev.ps1 down        - Stop development environment"
        Write-Host "  ./dev.ps1 build       - Build/rebuild development Docker images"
        Write-Host "  ./dev.ps1 restart     - Restart development stack"
        Write-Host "  ./dev.ps1 bash        - Open interactive shell in app container"
        Write-Host "  ./dev.ps1 test        - Run PHPUnit test suite inside container"
        Write-Host "  ./dev.ps1 migrate     - Run database migrations"
        Write-Host "  ./dev.ps1 seed        - Run database seeders"
        Write-Host "  ./dev.ps1 artisan ... - Run any artisan command"
        Write-Host "  ./dev.ps1 logs        - Stream container logs"
        Write-Host "  ./dev.ps1 prod-build  - Build production Docker image"
        Write-Host "  ./dev.ps1 prod-up     - Start production stack"
        Write-Host "  ./dev.ps1 prod-down   - Stop production stack"
        Write-Host "  ./dev.ps1 clean       - Remove containers and volumes"
    }
}
