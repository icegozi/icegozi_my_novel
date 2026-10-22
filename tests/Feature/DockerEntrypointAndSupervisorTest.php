<?php

namespace Tests\Feature;

use Tests\TestCase;

class DockerEntrypointAndSupervisorTest extends TestCase
{
    public function test_entrypoint_script_exists_and_implements_role_routing(): void
    {
        $entrypointPath = base_path('docker/entrypoint.sh');
        $this->assertFileExists($entrypointPath);

        $content = file_get_contents($entrypointPath);
        $this->assertStringContainsString('CONTAINER_ROLE', $content);
        $this->assertStringContainsString('AUTORUN_MIGRATIONS', $content);
        $this->assertStringContainsString('php artisan migrate --force', $content);
        $this->assertStringContainsString('supervisord', $content);
        $this->assertStringContainsString('php-fpm', $content);
    }

    public function test_supervisord_configuration_manages_workers_and_scheduler(): void
    {
        $supervisorPath = base_path('docker/supervisor/supervisord.conf');
        $this->assertFileExists($supervisorPath);

        $content = file_get_contents($supervisorPath);
        $this->assertStringContainsString('[supervisord]', $content);
        $this->assertStringContainsString('nodaemon=true', $content);
        $this->assertStringContainsString('[program:php-fpm]', $content);
        $this->assertStringContainsString('[program:laravel-worker]', $content);
        $this->assertStringContainsString('[program:laravel-scheduler]', $content);
        $this->assertStringContainsString('queue:work', $content);
        $this->assertStringContainsString('schedule:run', $content);
    }

    public function test_healthcheck_script_validates_runtime_health(): void
    {
        $healthcheckPath = base_path('docker/healthcheck.sh');
        $this->assertFileExists($healthcheckPath);

        $content = file_get_contents($healthcheckPath);
        $this->assertStringContainsString('#!/bin/sh', $content);
        $this->assertStringContainsString('php-fpm', $content);
    }
}
