<?php

namespace Tests\Feature;

use Tests\TestCase;

class DockerComposeValidationTest extends TestCase
{
    public function test_mysql_configuration_contains_enterprise_tunings(): void
    {
        $myCnfPath = base_path('docker/mysql/my.cnf');
        $this->assertFileExists($myCnfPath);

        $content = file_get_contents($myCnfPath);
        $this->assertStringContainsString('character-set-server=utf8mb4', $content);
        $this->assertStringContainsString('collation-server=utf8mb4_unicode_ci', $content);
        $this->assertStringContainsString('innodb_buffer_pool_size', $content);
        $this->assertStringContainsString('max_connections', $content);
    }

    public function test_docker_compose_dev_defines_all_required_services(): void
    {
        $composePath = base_path('docker-compose.yml');
        $this->assertFileExists($composePath);

        $content = file_get_contents($composePath);
        $this->assertStringContainsString('services:', $content);
        $this->assertStringContainsString('app:', $content);
        $this->assertStringContainsString('web:', $content);
        $this->assertStringContainsString('mysql:', $content);
        $this->assertStringContainsString('redis:', $content);
        $this->assertStringContainsString('queue-worker:', $content);
        $this->assertStringContainsString('target: development', $content);
        $this->assertStringContainsString('networks:', $content);
        $this->assertStringContainsString('volumes:', $content);
    }

    public function test_docker_compose_prod_defines_resilient_topology(): void
    {
        $prodComposePath = base_path('docker-compose.prod.yml');
        $this->assertFileExists($prodComposePath);

        $content = file_get_contents($prodComposePath);
        $this->assertStringContainsString('services:', $content);
        $this->assertStringContainsString('target: production', $content);
        $this->assertStringContainsString('restart: always', $content);
        $this->assertStringContainsString('worker:', $content);
        $this->assertStringContainsString('scheduler:', $content);
    }

    public function test_docker_env_example_contains_container_hostnames(): void
    {
        $envExamplePath = base_path('.env.docker.example');
        $this->assertFileExists($envExamplePath);

        $content = file_get_contents($envExamplePath);
        $this->assertStringContainsString('DB_HOST=mysql', $content);
        $this->assertStringContainsString('REDIS_HOST=redis', $content);
        $this->assertStringContainsString('CACHE_STORE=redis', $content);
        $this->assertStringContainsString('QUEUE_CONNECTION=redis', $content);
    }

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
}


