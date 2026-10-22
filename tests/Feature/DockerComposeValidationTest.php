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
}
