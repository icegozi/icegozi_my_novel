<?php

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
        $phpIniPath = base_path('docker/php/php.ini');
        $this->assertFileExists($phpIniPath);
        $phpIni = file_get_contents($phpIniPath);
        $this->assertStringContainsString('upload_max_filesize = 64M', $phpIni);
        $this->assertStringContainsString('post_max_size = 64M', $phpIni);
        $this->assertStringContainsString('memory_limit = 512M', $phpIni);
        $this->assertStringContainsString('expose_php = Off', $phpIni);

        $opcacheIniPath = base_path('docker/php/opcache.ini');
        $this->assertFileExists($opcacheIniPath);
        $opcacheIni = file_get_contents($opcacheIniPath);
        $this->assertStringContainsString('opcache.enable=1', $opcacheIni);
        $this->assertStringContainsString('opcache.memory_consumption=256', $opcacheIni);

        $wwwConfPath = base_path('docker/php/www.conf');
        $this->assertFileExists($wwwConfPath);
        $wwwConf = file_get_contents($wwwConfPath);
        $this->assertStringContainsString('[www]', $wwwConf);
        $this->assertStringContainsString('listen = 9000', $wwwConf);
    }

    public function test_dockerignore_excludes_sensitive_and_ephemeral_paths(): void
    {
        $dockerignorePath = base_path('.dockerignore');
        $this->assertFileExists($dockerignorePath);
        $dockerignore = file_get_contents($dockerignorePath);
        $this->assertStringContainsString('.env', $dockerignore);
        $this->assertStringContainsString('.git', $dockerignore);
        $this->assertStringContainsString('node_modules', $dockerignore);
        $this->assertStringContainsString('vendor', $dockerignore);
        $this->assertStringContainsString('tests', $dockerignore);
    }
}
