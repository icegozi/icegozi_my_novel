# Kế Hoạch 5: Đóng Gói Môi Trường Chạy Docker Chuẩn Enterprise (Enterprise Docker Packaging)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Xây dựng hệ thống container hóa chuẩn Enterprise toàn diện cho nền tảng 3D Novel Platform (Laravel 11, PHP 8.3 FPM Alpine, Nginx, Redis, MySQL, Supervisor queue worker, cron scheduler, multi-stage build, zero-downtime healthchecks và bảo mật non-root).

**Architecture:** Sử dụng kiến trúc multi-stage Docker build tách biệt khâu biên dịch Vite assets (Node 20), tối ưu hóa composer dependencies (Composer 2) và môi trường runtime siêu nhẹ (PHP 8.3 FPM Alpine). Tách biệt các container dịch vụ độc lập (`app`, `nginx`, `worker`, `scheduler`, `mysql`, `redis`, `mailpit`) kết nối qua bridge network bảo mật, điều phối bởi `docker-compose.yml` (development) và `docker-compose.prod.yml` (production).

**Tech Stack:** Docker 29+, Docker Compose v2.x, PHP 8.3 FPM Alpine, Nginx 1.27 Alpine, Redis 7 Alpine, MySQL 8.0, Node.js 20 Alpine, Supervisor, POSIX Shell Scripts.

**Spec:** [docs/superpowers/plans/2026-10-21-novel-platform-with-3d-reader.md](docs/superpowers/plans/2026-10-21-novel-platform-with-3d-reader.md)

## Global Constraints

- **Multi-Stage Build & Kích Thước Tối Ưu:** Image runtime production sử dụng `php:8.3-fpm-alpine`, loại bỏ hoàn toàn dev tools (`composer`, `npm`, `gcc`, `make`) trong image cuối để đảm bảo dung lượng nhỏ (< 150MB uncompressed) và bề mặt tấn công tối thiểu.
- **Bảo Mật Non-Root User:** Tất cả tiến trình ứng dụng chạy dưới quyền người dùng không có đặc quyền (`appuser` / `www-data` UID:GID 1000:1000). Mã nguồn được cấp quyền read-only, chỉ các thư mục `storage` và `bootstrap/cache` được cấp quyền ghi.
- **Enterprise OPcache & JIT:** Cấu hình OPcache tối ưu hóa cho sản phẩm (`opcache.validate_timestamps=0` trên production, `opcache.memory_consumption=256MB`, JIT `1255`).
- **Nginx Tối Ưu Tệp 3D Lớn & Security Headers:** `client_max_body_size` nâng lên `64M` để đáp ứng tải lên mô hình 3D GLB và ảnh định danh CCCD; thiết lập đầy đủ HTTP security headers (`HSTS`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Content-Security-Policy`).
- **Xử Lý Hàng Đợi & Tác Vụ Định Kỳ Riêng Biệt:** Tách biệt queue worker (`php artisan queue:work --tries=3 --timeout=90`) và scheduler cron (`php artisan schedule:run`) thành các container độc lập bằng Supervisor.
- **Enterprise Healthchecks:** Tích hợp healthcheck cho mọi service (`/up` endpoint cho web/fpm, `redis-cli ping` cho Redis, `mysqladmin ping` cho MySQL).

## Review Focus

1. **Thất bại khi kết nối Database/Redis lúc container vừa khởi động (Race condition):** `entrypoint.sh` phải có cơ chế chờ (wait-for) với timeout kiểm tra cổng kết nối trước khi chạy migration và khởi động PHP-FPM.
2. **Lỗi phân quyền thư mục `storage/` khi mount volume ngoài máy host:** Script khởi động phải kiểm tra và tự động gán quyền `775` và sở hữu `www-data:www-data` cho các thư mục cache/log/app.
3. **Tràn bộ nhớ hoặc lỗi timeout khi upload file 3D GLB lớn (50MB):** Nginx và PHP-FPM phải đồng bộ các cấu hình `client_max_body_size 64M`, `upload_max_filesize = 64M`, `post_max_size = 64M`, `fastcgi_read_timeout 120s`.
4. **Lộ thông tin nhạy cảm qua Docker image:** `.dockerignore` phải chặn toàn bộ file bí mật `.env`, `.git`, `tests`, `node_modules`, và các file chứng chỉ cá nhân.
5. **Worker bị kẹt tiến trình khi gặp lỗi kết nối Redis/DB:** Supervisor phải thiết lập cờ `autorestart=true`, `stopsignal=TERM`, và `stopwaitsecs=60` để đảm bảo graceful termination.

---

### Task 1: Multi-Stage Production Dockerfile & Cấu Hình PHP 8.3 FPM Enterprise

**Files:**
- Create: `docker/php/Dockerfile`
- Create: `docker/php/php.ini`
- Create: `docker/php/opcache.ini`
- Create: `docker/php/www.conf`
- Create: `.dockerignore`
- Test: `tests/Feature/DockerPhpConfigTest.php`

**Interfaces:**
- Consumes: Mã nguồn Laravel 11, `composer.json`, `package.json`, Vite configuration.
- Produces: Dockerfile multi-stage hỗ trợ cả target `development` (kèm Xdebug, công cụ dev) và target `production` (tối ưu hóa OPcache, non-root user `appuser`, pre-compiled assets).

- [ ] **Step 1: Viết test kiểm tra tính toàn vẹn và tiêu chuẩn của Dockerfile, php.ini, opcache.ini và .dockerignore**

```php
// tests/Feature/DockerPhpConfigTest.php
namespace Tests\Feature;

use Tests\TestCase;

class DockerPhpConfigTest extends TestCase
{
    public function test_dockerfile_contains_multistage_and_security_standards(): void
    {
        $dockerfilePath = base_path('docker/php/Dockerfile');
        $this->assertFileExists($dockerfilePath);

        $content = file_get_contents($dockerfilePath);
        $this->assertStringContainsString('FROM php:8.3-fpm-alpine', $content);
        $this->assertStringContainsString('AS node-builder', $content);
        $this->assertStringContainsString('AS composer-builder', $content);
        $this->assertStringContainsString('AS production', $content);
        $this->assertStringContainsString('AS development', $content);
        $this->assertStringContainsString('pdo_mysql', $content);
        $this->assertStringContainsString('pdo_pgsql', $content);
        $this->assertStringContainsString('redis', $content);
        $this->assertStringContainsString('opcache', $content);
        $this->assertStringContainsString('USER appuser', $content);
    }

    public function test_php_and_opcache_configs_meet_enterprise_spec(): void
    {
        $phpIni = file_get_contents(base_path('docker/php/php.ini'));
        $this->assertStringContainsString('upload_max_filesize = 64M', $phpIni);
        $this->assertStringContainsString('post_max_size = 64M', $phpIni);
        $this->assertStringContainsString('memory_limit = 512M', $phpIni);
        $this->assertStringContainsString('expose_php = Off', $phpIni);

        $opcacheIni = file_get_contents(base_path('docker/php/opcache.ini'));
        $this->assertStringContainsString('opcache.enable=1', $opcacheIni);
        $this->assertStringContainsString('opcache.memory_consumption=256', $opcacheIni);
    }

    public function test_dockerignore_excludes_sensitive_and_ephemeral_paths(): void
    {
        $dockerignore = file_get_contents(base_path('.dockerignore'));
        $this->assertStringContainsString('.env', $dockerignore);
        $this->assertStringContainsString('.git', $dockerignore);
        $this->assertStringContainsString('node_modules', $dockerignore);
        $this->assertStringContainsString('vendor', $dockerignore);
        $this->assertStringContainsString('tests', $dockerignore);
    }
}
```

- [ ] **Step 2: Chạy test xác nhận fail**

Run: `php artisan test --filter=DockerPhpConfigTest`
Expected: FAIL với File does not exist

- [ ] **Step 3: Triển khai Dockerfile, php.ini, opcache.ini, www.conf và .dockerignore**

Tạo các file cấu hình tại `docker/php/` và `.dockerignore` tại thư mục gốc đáp ứng đầy đủ tiêu chuẩn multi-stage, bảo mật non-root và dung lượng tối thiểu.

- [ ] **Step 4: Chạy test xác nhận pass**

Run: `php artisan test --filter=DockerPhpConfigTest`
Expected: PASS

- [ ] **Step 5: Commit mã nguồn Task 1**

```bash
git add docker/php/ .dockerignore tests/Feature/DockerPhpConfigTest.php
git commit -m "feat(docker): implement enterprise multi-stage php-fpm dockerfile and runtime configs"
```

---

### Task 2: Reverse Proxy Nginx Tối Ưu Tệp 3D & Security Headers

**Files:**
- Create: `docker/nginx/Dockerfile`
- Create: `docker/nginx/nginx.conf`
- Create: `docker/nginx/default.conf`
- Test: `tests/Feature/DockerNginxConfigTest.php`

**Interfaces:**
- Consumes: PHP-FPM service tại cổng `app:9000`, đường dẫn public của Laravel `/var/www/public`.
- Produces: Web server hiệu năng cao điều hướng request tĩnh, nén Gzip/Brotli, proxy FastCGI và kiểm tra sức khỏe tại `/up`.

- [ ] **Step 1: Viết test kiểm tra cấu hình Nginx và các thiết lập bảo mật & upload dung lượng lớn**

```php
// tests/Feature/DockerNginxConfigTest.php
namespace Tests\Feature;

use Tests\TestCase;

class DockerNginxConfigTest extends TestCase
{
    public function test_nginx_files_exist_and_have_required_directives(): void
    {
        $this->assertFileExists(base_path('docker/nginx/Dockerfile'));
        $this->assertFileExists(base_path('docker/nginx/nginx.conf'));
        $this->assertFileExists(base_path('docker/nginx/default.conf'));

        $nginxConf = file_get_contents(base_path('docker/nginx/nginx.conf'));
        $this->assertStringContainsString('gzip on;', $nginxConf);
        $this->assertStringContainsString('gzip_types', $nginxConf);

        $defaultConf = file_get_contents(base_path('docker/nginx/default.conf'));
        $this->assertStringContainsString('client_max_body_size 64M;', $defaultConf);
        $this->assertStringContainsString('fastcgi_pass app:9000;', $defaultConf);
        $this->assertStringContainsString('location /up', $defaultConf);
        $this->assertStringContainsString('X-Frame-Options', $defaultConf);
        $this->assertStringContainsString('X-Content-Type-Options', $defaultConf);
    }
}
```

- [ ] **Step 2: Chạy test xác nhận fail**

Run: `php artisan test --filter=DockerNginxConfigTest`
Expected: FAIL với File does not exist

- [ ] **Step 3: Triển khai Nginx Dockerfile, nginx.conf và default.conf**

Thiết lập Nginx Alpine với:
- `client_max_body_size 64M`
- Security headers: `X-Frame-Options SAMEORIGIN`, `X-Content-Type-Options nosniff`, `X-XSS-Protection "1; mode=block"`
- FastCGI buffer sizes: `fastcgi_buffer_size 32k; fastcgi_buffers 8 16k;`
- Caching headers cho file tĩnh 3D `.glb`, `.gltf`, ảnh, font chữ (`max-age=31536000, immutable`).
- Location `/up` phục vụ healthcheck của Laravel 11.

- [ ] **Step 4: Chạy test xác nhận pass**

Run: `php artisan test --filter=DockerNginxConfigTest`
Expected: PASS

- [ ] **Step 5: Commit mã nguồn Task 2**

```bash
git add docker/nginx/ tests/Feature/DockerNginxConfigTest.php
git commit -m "feat(docker): implement enterprise nginx reverse proxy with 3d assets tuning and security headers"
```

---

### Task 3: Background Worker, Scheduler và Kịch Bản Khởi Động Tự Động (Entrypoint & Healthcheck)

**Files:**
- Create: `docker/supervisor/supervisord.conf`
- Create: `docker/entrypoint.sh`
- Create: `docker/healthcheck.sh`
- Test: `tests/Feature/DockerEntrypointAndSupervisorTest.php`

**Interfaces:**
- Consumes: Môi trường biến `RUN_MIGRATIONS`, `APP_ENV`, `CONTAINER_ROLE` (`app`, `worker`, `scheduler`).
- Produces: Entrypoint tự động kiểm tra database readiness, dọn dẹp và cache cấu hình, điều hướng chạy Web, Queue Worker hoặc Scheduler cron.

- [ ] **Step 1: Viết test kiểm tra logic của Supervisor, Entrypoint và Healthcheck scripts**

```php
// tests/Feature/DockerEntrypointAndSupervisorTest.php
namespace Tests\Feature;

use Tests\TestCase;

class DockerEntrypointAndSupervisorTest extends TestCase
{
    public function test_supervisor_config_contains_worker_and_scheduler_programs(): void
    {
        $path = base_path('docker/supervisor/supervisord.conf');
        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('[program:laravel-worker]', $content);
        $this->assertStringContainsString('php artisan queue:work', $content);
        $this->assertStringContainsString('[program:laravel-scheduler]', $content);
        $this->assertStringContainsString('php artisan schedule:run', $content);
        $this->assertStringContainsString('autorestart=true', $content);
    }

    public function test_entrypoint_script_handles_database_check_and_roles(): void
    {
        $path = base_path('docker/entrypoint.sh');
        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('#!/bin/sh', $content);
        $this->assertStringContainsString('set -e', $content);
        $this->assertStringContainsString('CONTAINER_ROLE', $content);
        $this->assertStringContainsString('RUN_MIGRATIONS', $content);
        $this->assertStringContainsString('php artisan config:cache', $content);
        $this->assertStringContainsString('exec', $content);
    }

    public function test_healthcheck_script_verifies_runtime_liveness(): void
    {
        $path = base_path('docker/healthcheck.sh');
        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('#!/bin/sh', $content);
        $this->assertStringContainsString('php-fpm', $content);
    }
}
```

- [ ] **Step 2: Chạy test xác nhận fail**

Run: `php artisan test --filter=DockerEntrypointAndSupervisorTest`
Expected: FAIL

- [ ] **Step 3: Triển khai Supervisor config, entrypoint.sh và healthcheck.sh**

- `supervisord.conf`: Quản lý song song `laravel-worker` (queue processor) và `laravel-scheduler` (lặp định kỳ `schedule:run`).
- `entrypoint.sh`: Chờ kết nối Database / Redis sẵn sàng qua nc / php sockets, phân nhánh theo `CONTAINER_ROLE`:
  - `app`: Tối ưu cache nếu `APP_ENV=production`, chạy `php artisan migrate --force` nếu `RUN_MIGRATIONS=true`, khởi động `php-fpm`.
  - `worker`: Chạy `php artisan queue:work --tries=3 --timeout=90`.
  - `scheduler`: Chạy vòng lặp cron scheduler.
- `healthcheck.sh`: Kiểm tra trạng thái sống của PHP-FPM tiến trình.

- [ ] **Step 4: Chạy test xác nhận pass**

Run: `php artisan test --filter=DockerEntrypointAndSupervisorTest`
Expected: PASS

- [ ] **Step 5: Commit mã nguồn Task 3**

```bash
git add docker/supervisor/ docker/entrypoint.sh docker/healthcheck.sh tests/Feature/DockerEntrypointAndSupervisorTest.php
git commit -m "feat(docker): implement supervisor daemon, entrypoint orchestration, and healthchecks"
```

---

### Task 4: Điều Phối Đa Dịch Vụ Docker Compose & Cấu Hình MySQL 8.0 Enterprise

**Files:**
- Create: `docker/mysql/my.cnf`
- Create: `docker-compose.yml`
- Create: `docker-compose.prod.yml`
- Create: `.env.docker.example`
- Test: `tests/Feature/DockerComposeValidationTest.php`

**Interfaces:**
- Consumes: Image PHP FPM, Nginx, MySQL 8.0, Redis 7, Mailpit.
- Produces: Cấu hình cơ sở dữ liệu MySQL 8.0 tối ưu utf8mb4 & InnoDB buffer pool, cùng bộ tệp Docker Compose hoàn chỉnh sẵn sàng `docker compose up -d` cục bộ hoặc triển khai trên máy chủ sản phẩm Kubernetes/Docker Swarm/Compose.

- [ ] **Step 1: Viết test kiểm tra tính hợp lệ của Compose specs, MySQL my.cnf và biến môi trường mẫu**

```php
// tests/Feature/DockerComposeValidationTest.php
namespace Tests\Feature;

use Tests\TestCase;

class DockerComposeValidationTest extends TestCase
{
    public function test_mysql_enterprise_config_exists(): void
    {
        $myCnfPath = base_path('docker/mysql/my.cnf');
        $this->assertFileExists($myCnfPath);

        $content = file_get_contents($myCnfPath);
        $this->assertStringContainsString('character-set-server=utf8mb4', $content);
        $this->assertStringContainsString('collation-server=utf8mb4_unicode_ci', $content);
        $this->assertStringContainsString('innodb_buffer_pool_size', $content);
    }

    public function test_docker_compose_files_define_all_enterprise_services(): void
    {
        $composePath = base_path('docker-compose.yml');
        $this->assertFileExists($composePath);

        $content = file_get_contents($composePath);
        $this->assertStringContainsString('services:', $content);
        $this->assertStringContainsString('nginx:', $content);
        $this->assertStringContainsString('app:', $content);
        $this->assertStringContainsString('worker:', $content);
        $this->assertStringContainsString('scheduler:', $content);
        $this->assertStringContainsString('mysql:', $content);
        $this->assertStringContainsString('redis:', $content);
        $this->assertStringContainsString('networks:', $content);
        $this->assertStringContainsString('volumes:', $content);
    }

    public function test_docker_compose_prod_defines_resource_limits_and_restart_policy(): void
    {
        $prodPath = base_path('docker-compose.prod.yml');
        $this->assertFileExists($prodPath);

        $content = file_get_contents($prodPath);
        $this->assertStringContainsString('restart: unless-stopped', $content);
        $this->assertStringContainsString('healthcheck:', $content);
        $this->assertStringContainsString('deploy:', $content);
    }

    public function test_env_docker_example_has_matching_service_hosts(): void
    {
        $envPath = base_path('.env.docker.example');
        $this->assertFileExists($envPath);

        $content = file_get_contents($envPath);
        $this->assertStringContainsString('DB_HOST=mysql', $content);
        $this->assertStringContainsString('REDIS_HOST=redis', $content);
        $this->assertStringContainsString('QUEUE_CONNECTION=redis', $content);
        $this->assertStringContainsString('CACHE_STORE=redis', $content);
    }
}
```

- [ ] **Step 2: Chạy test xác nhận fail**

Run: `php artisan test --filter=DockerComposeValidationTest`
Expected: FAIL

- [ ] **Step 3: Triển khai docker/mysql/my.cnf, docker-compose.yml, docker-compose.prod.yml và .env.docker.example**

- `docker/mysql/my.cnf`: Cấu hình MySQL 8.0 Enterprise tối ưu bộ mã UTF-8 đầy đủ (`utf8mb4`), bộ đệm InnoDB (`innodb_buffer_pool_size`), thời gian chờ và giới hạn kết nối (`max_connections=500`).
- `docker-compose.yml`: Stack môi trường phát triển đầy đủ với volume mount trực tiếp để live-reload code, dịch vụ mailpit để bắt email kiểm thử.
- `docker-compose.prod.yml`: Stack môi trường production với resource limits (CPU/RAM limits), không mount mã nguồn trực tiếp (sử dụng image đóng gói sẵn), cấu hình logging `json-file` có xoay vòng (log-rotate) tránh đầy ổ cứng.
- `.env.docker.example`: Biến môi trường chuẩn hóa tương thích tuyệt đối với các tên service mạng nội bộ `mysql`, `redis`.

- [ ] **Step 4: Chạy test xác nhận pass**

Run: `php artisan test --filter=DockerComposeValidationTest`
Expected: PASS

- [ ] **Step 5: Kiểm tra cú pháp Compose qua Docker CLI (nếu khả dụng)**

Run: `docker compose config`
Expected: Thoát với exit code 0, cú pháp hợp lệ không có lỗi cấu trúc YAML.

- [ ] **Step 6: Commit mã nguồn Task 4**

```bash
git add docker-compose.yml docker-compose.prod.yml .env.docker.example tests/Feature/DockerComposeValidationTest.php
git commit -m "feat(docker): implement enterprise docker compose orchestration for dev and prod"
```

---

### Task 5: Tiện Ích Tối Ưu Thao Tác Nhà Phát Triển (Makefile & Tài Liệu Vận Hành)

**Files:**
- Create: `Makefile`
- Create: `docs/deployment/docker-enterprise-guide.md`
- Test: `tests/Feature/DockerDeveloperErgonomicsTest.php`

**Interfaces:**
- Consumes: `docker compose` commands.
- Produces: Bộ lệnh tiện ích `make up`, `make down`, `make test`, `make logs`, `make migrate`, `make shell` và tài liệu hướng dẫn vận hành chuẩn Enterprise cho đội ngũ DevOps.

- [ ] **Step 1: Viết test kiểm tra sự tồn tại của Makefile và tài liệu hướng dẫn**

```php
// tests/Feature/DockerDeveloperErgonomicsTest.php
namespace Tests\Feature;

use Tests\TestCase;

class DockerDeveloperErgonomicsTest extends TestCase
{
    public function test_makefile_contains_essential_developer_targets(): void
    {
        $path = base_path('Makefile');
        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('up:', $content);
        $this->assertStringContainsString('down:', $content);
        $this->assertStringContainsString('build:', $content);
        $this->assertStringContainsString('test:', $content);
        $this->assertStringContainsString('migrate:', $content);
        $this->assertStringContainsString('logs:', $content);
    }

    public function test_deployment_guide_is_documented(): void
    {
        $path = base_path('docs/deployment/docker-enterprise-guide.md');
        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('Hướng Dẫn Vận Hành Docker Enterprise', $content);
        $this->assertStringContainsString('Nghị định 147/2024/NĐ-CP', $content);
    }
}
```

- [ ] **Step 2: Chạy test xác nhận fail**

Run: `php artisan test --filter=DockerDeveloperErgonomicsTest`
Expected: FAIL

- [ ] **Step 3: Triển khai Makefile và docs/deployment/docker-enterprise-guide.md**

- `Makefile`: Cung cấp giao diện dòng lệnh ngắn gọn, tiện dụng cho lập trình viên.
- `docs/deployment/docker-enterprise-guide.md`: Hướng dẫn chi tiết từng bước xây dựng image, cấu hình TLS/HTTPS, cấu hình persistent storage, sao lưu dữ liệu MySQL/Redis và cơ chế xoay vòng log an ninh mạng theo NĐ 147/2024.

- [ ] **Step 4: Chạy test xác nhận pass**

Run: `php artisan test --filter=DockerDeveloperErgonomicsTest`
Expected: PASS

- [ ] **Step 5: Chạy toàn bộ test suite để đảm bảo không làm gián đoạn hệ thống**

Run: `php artisan test`
Expected: Tất cả 95+ test suites đều PASS 100%.

- [ ] **Step 6: Commit mã nguồn Task 5**

```bash
git add Makefile docs/deployment/ tests/Feature/DockerDeveloperErgonomicsTest.php
git commit -m "docs(docker): provide makefile automation and enterprise deployment guide"
```
