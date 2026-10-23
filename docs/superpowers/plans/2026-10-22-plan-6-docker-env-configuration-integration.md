# Docker Environment Configuration from .env Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Synchronize and dynamically drive all Docker Compose development and production infrastructure configurations directly from the `.env` file, enabling seamless dual-mode execution (host CLI and container runtime) without hardcoded credentials or port collisions.

**Architecture:** Docker Compose natively reads `.env` for variable interpolation (`${VAR:-default}`) and service injection via `env_file: .env`. We will parameterize host-bound port forwards (`FORWARD_NGINX_PORT`, `FORWARD_DB_PORT`, `FORWARD_REDIS_PORT`), align MySQL container credentials directly with Laravel's `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, and use precedence-based internal host override (`DB_HOST: ${DOCKER_DB_HOST:-mysql}`) so that both host CLI (`127.0.0.1`) and Docker containers (`mysql`) operate flawlessly with a single shared `.env` file.

**Tech Stack:** Docker Compose v2, Laravel 11 Dotenv, MySQL 8.0, Redis 7, PHPUnit 11.

**Spec:** Integration requirement to allow `.env` to configure all container parameters (ports, database name, user, passwords, internal routing) seamlessly.

## Global Constraints

- Never break backward compatibility for existing containers or default setups (always provide robust `:-fallback` values in Compose files).
- The host `.env` must remain compatible with native host CLI commands (`php artisan test`, `php artisan migrate`) using `DB_HOST=127.0.0.1`.
- Container environments (`app`, `queue-worker`) must automatically resolve database and cache services using Docker network names (`mysql`, `redis`) without requiring separate conflicting `.env` files.
- All Docker Compose configurations must validate cleanly via `docker compose config`.
- All automated tests must follow Test-Driven Development (TDD) with Red -> Green verification before git commits.

## Review Focus

1. **Port Collisions on Host**: If port 8080 or 3306 is occupied on the developer machine, setting `FORWARD_NGINX_PORT=8088` or `FORWARD_DB_PORT=3307` in `.env` must remap published ports without modifying `docker-compose.yml`.
2. **Credential Desynchronization**: If developer changes `DB_PASSWORD` or `DB_DATABASE` in `.env`, MySQL container creation and Laravel application connection must match automatically.
3. **Dual Host/Container Execution**: Running `php artisan migrate` on host (`127.0.0.1`) and `docker compose exec app php artisan migrate` (`mysql`) must both successfully connect to the same MySQL instance.
4. **Missing .env Graceful Fallback**: If `.env` lacks optional Docker variables, Compose must smoothly fall back to enterprise defaults without crashing.
5. **Production Secret Enforcement**: In `docker-compose.prod.yml`, production variables must accept overrides from environment or `.env` and prevent default insecure fallbacks when deployed.

---

### Task 1: Parameterize Docker Compose Ports and Network Envs with Fallbacks

**Files:**
- Modify: `docker-compose.yml:35-50, 75-120`
- Modify: `.env.example:20-55`
- Modify: `.env.docker.example:1-40`
- Test: `tests/Feature/DockerComposeValidationTest.php`

**Interfaces:**
- Consumes: Existing `docker-compose.yml` service definitions (`web`, `mysql`, `redis`)
- Produces: Dynamic `${FORWARD_NGINX_PORT:-8080}`, `${FORWARD_DB_PORT:-3306}`, `${FORWARD_REDIS_PORT:-6379}` port bindings in `docker-compose.yml`

- [ ] **Step 1: Write the failing test**

In `tests/Feature/DockerComposeValidationTest.php`, add test `test_docker_compose_supports_dynamic_port_and_credential_interpolation`:
```php
public function test_docker_compose_supports_dynamic_port_and_credential_interpolation(): void
{
    $composePath = base_path('docker-compose.yml');
    $content = file_get_contents($composePath);

    // Verify dynamic port mappings with fallbacks
    $this->assertStringContainsString('${FORWARD_NGINX_PORT:-8080}:80', $content);
    $this->assertStringContainsString('${FORWARD_DB_PORT:-3306}:3306', $content);
    $this->assertStringContainsString('${FORWARD_REDIS_PORT:-6379}:6379', $content);

    // Verify MySQL credentials driven by .env variables
    $this->assertStringContainsString('${DB_DATABASE:-icegozi_novel}', $content);
    $this->assertStringContainsString('${DB_USERNAME:-novel_user}', $content);
    $this->assertStringContainsString('${DB_PASSWORD:-novel_secret_password}', $content);
    $this->assertStringContainsString('${MYSQL_ROOT_PASSWORD:-root_secret_password}', $content);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DockerComposeValidationTest::test_docker_compose_supports_dynamic_port_and_credential_interpolation`
Expected: FAIL with missing assertion strings in `docker-compose.yml`.

- [ ] **Step 3: Implement dynamic interpolation in `docker-compose.yml` and template files**

Update `docker-compose.yml`:
1. In `web` service: `ports: - "${FORWARD_NGINX_PORT:-8080}:80"`
2. In `mysql` service:
   ```yaml
   ports:
     - "${FORWARD_DB_PORT:-3306}:3306"
   environment:
     MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD:-root_secret_password}
     MYSQL_DATABASE: ${DB_DATABASE:-icegozi_novel}
     MYSQL_USER: ${DB_USERNAME:-novel_user}
     MYSQL_PASSWORD: ${DB_PASSWORD:-novel_secret_password}
   ```
3. In `redis` service: `ports: - "${FORWARD_REDIS_PORT:-6379}:6379"`
4. Update `.env.example` and `.env.docker.example` with the documented port configuration block:
   ```dotenv
   FORWARD_NGINX_PORT=8080
   FORWARD_DB_PORT=3306
   FORWARD_REDIS_PORT=6379
   ```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=DockerComposeValidationTest`
Expected: PASS (all assertions pass).
Run: `docker compose config`
Expected: Valid compose config with exit code 0.

- [ ] **Step 5: Commit**

```bash
git add docker-compose.yml .env.example .env.docker.example tests/Feature/DockerComposeValidationTest.php
git commit -m "feat(docker): parameterize compose ports and mysql credentials with .env interpolation"
```

---

### Task 2: Implement `env_file: .env` Injection and Dual Host/Container Routing

**Files:**
- Modify: `docker-compose.yml:10-30, 55-75`
- Modify: `.env:24-35`
- Test: `tests/Feature/DockerComposeValidationTest.php`

**Interfaces:**
- Consumes: Laravel `.env` variables (`APP_KEY`, `APP_DEBUG`, `DB_*`, `REDIS_*`, `MAIL_*`)
- Produces: Full environment injection into `app` and `queue-worker` with container-internal overrides

- [ ] **Step 1: Write the failing test**

In `tests/Feature/DockerComposeValidationTest.php`, add test `test_docker_compose_injects_env_file_with_container_host_overrides`:
```php
public function test_docker_compose_injects_env_file_with_container_host_overrides(): void
{
    $composePath = base_path('docker-compose.yml');
    $content = file_get_contents($composePath);

    // Verify env_file injection in app and worker
    $this->assertStringContainsString('env_file:', $content);
    $this->assertStringContainsString('- .env', $content);

    // Verify container-specific host overrides
    $this->assertStringContainsString('DB_HOST: ${DOCKER_DB_HOST:-mysql}', $content);
    $this->assertStringContainsString('REDIS_HOST: ${DOCKER_REDIS_HOST:-redis}', $content);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DockerComposeValidationTest::test_docker_compose_injects_env_file_with_container_host_overrides`
Expected: FAIL with missing `env_file:` or `DOCKER_DB_HOST`.

- [ ] **Step 3: Implement `env_file` and routing overrides in `docker-compose.yml` and `.env`**

1. In `docker-compose.yml`:
   Under `app:`:
   ```yaml
   env_file:
     - .env
   environment:
     CONTAINER_ROLE: app
     DB_HOST: ${DOCKER_DB_HOST:-mysql}
     REDIS_HOST: ${DOCKER_REDIS_HOST:-redis}
   ```
   Under `queue-worker:`:
   ```yaml
   env_file:
     - .env
   environment:
     CONTAINER_ROLE: worker
     DB_HOST: ${DOCKER_DB_HOST:-mysql}
     REDIS_HOST: ${DOCKER_REDIS_HOST:-redis}
   ```

2. In `.env`:
   Ensure database section has synchronized credentials:
   ```dotenv
   FORWARD_NGINX_PORT=8080
   FORWARD_DB_PORT=3306
   FORWARD_REDIS_PORT=6379

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=icegozi_novel
   DB_USERNAME=novel_user
   DB_PASSWORD=novel_secret_password
   MYSQL_ROOT_PASSWORD=root_secret_password

   DOCKER_DB_HOST=mysql
   DOCKER_REDIS_HOST=redis
   ```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=DockerComposeValidationTest`
Expected: PASS.
Run: `docker compose config`
Expected: Output verifies that all `.env` values are correctly interpolated into containers.

- [ ] **Step 5: Commit**

```bash
git add docker-compose.yml .env.example .env.docker.example tests/Feature/DockerComposeValidationTest.php
git commit -m "feat(docker): enable .env file injection with container host routing overrides"
```

---

### Task 3: Production Hardening, Live Verification & Operations Documentation

**Files:**
- Modify: `docker-compose.prod.yml`
- Modify: `docs/deployment/docker-enterprise-guide.md`
- Test: `tests/Feature/DockerDeveloperErgonomicsTest.php`

**Interfaces:**
- Consumes: Task 1 and Task 2 `.env` integration patterns
- Produces: Production environment configuration guide and live container restart validation

- [ ] **Step 1: Write the failing test**

In `tests/Feature/DockerDeveloperErgonomicsTest.php`, add test `test_deployment_guide_documents_env_configuration_matrix`:
```php
public function test_deployment_guide_documents_env_configuration_matrix(): void
{
    $guidePath = base_path('docs/deployment/docker-enterprise-guide.md');
    $content = file_get_contents($guidePath);

    $this->assertStringContainsString('FORWARD_NGINX_PORT', $content);
    $this->assertStringContainsString('FORWARD_DB_PORT', $content);
    $this->assertStringContainsString('DOCKER_DB_HOST', $content);
    $this->assertStringContainsString('Dual Host/Container Execution', $content);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DockerDeveloperErgonomicsTest::test_deployment_guide_documents_env_configuration_matrix`
Expected: FAIL.

- [ ] **Step 3: Update `docker-compose.prod.yml` and `docs/deployment/docker-enterprise-guide.md`**

1. In `docker-compose.prod.yml`:
   Ensure `env_file: .env` or environment interpolation matches standard production variable names:
   ```yaml
   env_file:
     - .env
   environment:
     CONTAINER_ROLE: app
     APP_ENV: production
     DB_HOST: ${DOCKER_DB_HOST:-mysql}
     REDIS_HOST: ${DOCKER_REDIS_HOST:-redis}
   ```
2. In `docs/deployment/docker-enterprise-guide.md`:
   Add section "7. Environment Variable Reference & Dual Host/Container Architecture" detailing:
   - Variable table (`FORWARD_NGINX_PORT`, `FORWARD_DB_PORT`, `FORWARD_REDIS_PORT`, `DB_HOST`, `DOCKER_DB_HOST`, etc.).
   - Explanation of how host tools connect via `127.0.0.1:3306` while container processes connect via `mysql:3306`.
   - Instructions on overriding default ports to prevent host port binding conflicts.
3. Live Container Verification:
   Run `docker compose up -d` to reload container definitions with `.env` settings.
   Verify `docker compose ps` shows all containers healthy.
   Verify `php artisan test` on host and `docker compose exec app php artisan test` both pass.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=Docker`
Expected: PASS (all Docker feature tests pass).

- [ ] **Step 5: Commit**

```bash
git add docker-compose.prod.yml docs/deployment/docker-enterprise-guide.md tests/Feature/DockerDeveloperErgonomicsTest.php
git commit -m "docs(docker): document .env configuration matrix and dual host-container architecture"
```

---

## Plan Self-Review Checklist

1. **Spec Coverage**: Plan explicitly addresses setting up Docker configuration directly from `.env` (ports, credentials, dual-network resolution, production settings).
2. **Step Scan**: Every step contains exact file paths, explicit test code, terminal verification commands, and git commits.
3. **Type Consistency**: All variables (`FORWARD_NGINX_PORT`, `FORWARD_DB_PORT`, `FORWARD_REDIS_PORT`, `DOCKER_DB_HOST`, `DOCKER_REDIS_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`) are named consistently across Compose files, `.env` templates, tests, and documentation.
4. **Review Focus**: Covers port collisions, credential sync, dual host/container execution, fallback handling, and production hardening.
5. **Proportion**: Concise, actionable, and strictly task-oriented without redundant prose.
