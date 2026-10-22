# Enterprise Docker Architecture & Operations Guide

## 1. System Architecture Overview

The Icegozi Novel Platform packaging is structured for high availability, security hardening, and high-performance processing of novel chapters, audio narration, and 3D avatar/scene assets (`.glb`, `.gltf`).

```mermaid
graph TD
    Client([Web & Mobile Clients]) -->|HTTP / HTTPS: 80, 443| Nginx[Nginx Reverse Proxy\n(1.27-alpine)]
    
    subgraph Container Cluster [Docker Isolated Network: app-network]
        Nginx -->|FastCGI :9000| App[PHP 8.3 FPM App\n(Web Request Handler)]
        Nginx -->|Static Cache| Assets[Static Assets & 3D Models]
        
        App -->|Query & Write| MySQL[(MySQL 8.0 Enterprise\nutf8mb4 / InnoDB 512M)]
        App -->|Cache & Session| Redis[(Redis 7 In-Memory\nAppendOnly)]
        
        Worker[Supervisor Queue Worker\n(Novel Processing & Transcoding)] --> MySQL
        Worker --> Redis
        
        Scheduler[Laravel Cron Scheduler\n(Hourly/Daily Tasks)] --> App
    end
```

---

## 2. 3D Asset & Media Pipeline Tuning (GLB / GLTF)

The platform supports 3D avatar customizers and interactive scene visualizers requiring transfer of large binary models:
1. **Upload Size Capacity**: `client_max_body_size 64M;` is configured in `docker/nginx/default.conf` and `upload_max_filesize = 64M` / `post_max_size = 64M` in `docker/php/php.ini`.
2. **MIME Types**: Custom MIME associations ensure correct content-type delivery:
   - `.glb` $\rightarrow$ `model/gltf-binary`
   - `.gltf` $\rightarrow$ `model/gltf+json`
3. **FastCGI Buffer Tuning**: `fastcgi_buffers 16 16k;` and `fastcgi_buffer_size 32k;` avoid disk buffer spooling during large payload negotiations.
4. **Compression Strategy**: High-ratio Gzip compression enabled for GLTF JSON and text assets, while avoiding redundant compression overhead on pre-compressed binary GLB meshes.

---

## 3. Local Development Runbook

### Prerequisites
- Docker Engine $\ge$ 24.0 or Docker Desktop with Compose v2
- Git

### Initial Setup
1. Clone the repository and navigate to the project root:
   ```bash
   cd icegozi_my_novel
   ```
2. Copy environment template:
   ```bash
   cp .env.docker.example .env
   ```
3. Generate application encryption key (if not already generated):
   ```bash
   php artisan key:generate
   ```
4. Build and start development environment:
   ```bash
   make build
   make up
   ```
5. Run migrations:
   ```bash
   make migrate
   ```
6. Access application in browser:
   - Web application: `http://localhost:8080`
   - Healthcheck: `http://localhost:8080/up`

---

## 4. Production Deployment Runbook

### Build Production Artifacts
```bash
docker compose -f docker-compose.prod.yml build
```

### Pre-deployment Checklist
- [ ] Ensure `.env` is populated with strong production credentials (`MYSQL_ROOT_PASSWORD`, `DB_PASSWORD`, `APP_KEY`).
- [ ] Ensure `APP_DEBUG=false` and `APP_ENV=production`.
- [ ] Validate SSL termination on host or upstream load balancer (AWS ALB, Cloudflare, Traefik).

### Starting the Production Cluster
```bash
docker compose -f docker-compose.prod.yml up -d
```

### Zero-Downtime Rolling Reload
When releasing new application versions without downtime:
```bash
# 1. Pull / build latest production image
docker compose -f docker-compose.prod.yml build app worker

# 2. Re-create app container
docker compose -f docker-compose.prod.yml up -d --no-deps app

# 3. Gracefully restart workers to reload codebase
docker compose -f docker-compose.prod.yml exec worker php artisan queue:restart
```

---

## 5. Backup and Disaster Recovery

### Automated MySQL Database Backup
To perform an enterprise online hot backup with character set consistency:
```bash
docker compose exec mysql mysqldump \
  -u root -p"$MYSQL_ROOT_PASSWORD" \
  --default-character-set=utf8mb4 \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  icegozi_novel | gzip > backup_novel_$(date +%Y%m%d_%H%M%S).sql.gz
```

### Restoring Database from Backup
```bash
gunzip < backup_novel_20261022.sql.gz | docker compose exec -T mysql mysql \
  -u root -p"$MYSQL_ROOT_PASSWORD" \
  --default-character-set=utf8mb4 \
  icegozi_novel
```

### Redis State Persistence
Redis runs with `appendonly yes`. In-flight queues and cache state are backed up automatically to the named volume `redis_prod_data` (`/data/appendonlydir`).

---

## 6. Container Security Standards

1. **Non-Root Execution**: Both PHP-FPM processes and queue workers run under unprivileged user `appuser` (UID: 1000).
2. **Immutable Read-Only Codebase in Prod**: Production builds do not mount local files, guaranteeing image immutability.
3. **Hardened HTTP Headers**: Automatic insertion of `X-Frame-Options SAMEORIGIN`, `X-Content-Type-Options nosniff`, and `Referrer-Policy`.
4. **PHP Information Hiding**: `expose_php = Off` and `server_tokens off` prevent server banner leakage.

---

## 7. Environment Variable Reference & Dual Host/Container Architecture

### Dynamic .env Variable Reference

All Docker Compose parameters can be overridden cleanly from the project root `.env` without modifying repository compose files:

| Variable | Default | Purpose / Scope |
|---|---|---|
| `FORWARD_NGINX_PORT` | `8080` | Host port mapped to Nginx container port `80`. Modify if port 8080 is occupied. |
| `FORWARD_DB_PORT` | `3306` | Host port mapped to MySQL container port `3306` for local IDEs & host tools. |
| `FORWARD_REDIS_PORT` | `6379` | Host port mapped to Redis container port `6379`. |
| `DB_DATABASE` | `icegozi_novel` | Initialized database schema in MySQL container & Laravel connection. |
| `DB_USERNAME` | `novel_user` | Application user provisioned in MySQL container & Laravel connection. |
| `DB_PASSWORD` | `novel_secret_password` | Application user password in MySQL container & Laravel connection. |
| `MYSQL_ROOT_PASSWORD` | `root_secret_password` | Administrative root password for MySQL container. |
| `DOCKER_DB_HOST` | `mysql` | Internal Docker bridge network hostname for the database container. |
| `DOCKER_REDIS_HOST` | `redis` | Internal Docker bridge network hostname for the Redis container. |

### Dual Host/Container Execution Model

The platform architecture seamlessly allows concurrent execution between host development environments and Docker container environments using a single shared `.env` file:

1. **Host-Side Execution (Native CLI / IDEs)**:
   - When running native PHP commands (`php artisan migrate`, `php artisan test`) or connecting external database GUI tools (DBeaver, TablePlus), `DB_HOST=127.0.0.1` and `DB_PORT=3306` connect to MySQL through the published host port.
2. **Container-Side Execution (App & Worker)**:
   - When running inside Docker containers (`icegozi-novel-app`, `icegozi-novel-worker`), Docker Compose injects all Laravel environment keys via `env_file: .env`.
   - The `environment:` precedence block automatically maps `DB_HOST: ${DOCKER_DB_HOST:-mysql}` and `REDIS_HOST: ${DOCKER_REDIS_HOST:-redis}` so network traffic is routed internally within `app-network` directly between container IPs.

