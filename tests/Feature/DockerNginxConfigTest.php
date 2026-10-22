<?php

namespace Tests\Feature;

use Tests\TestCase;

class DockerNginxConfigTest extends TestCase
{
    public function test_nginx_dockerfile_and_main_config_exist_and_configured(): void
    {
        $dockerfilePath = base_path('docker/nginx/Dockerfile');
        $this->assertFileExists($dockerfilePath);
        $dockerfile = file_get_contents($dockerfilePath);
        $this->assertStringContainsString('FROM nginx:', $dockerfile);
        $this->assertStringContainsString('alpine', $dockerfile);

        $mainConfPath = base_path('docker/nginx/nginx.conf');
        $this->assertFileExists($mainConfPath);
        $mainConf = file_get_contents($mainConfPath);
        $this->assertStringContainsString('worker_processes', $mainConf);
        $this->assertStringContainsString('gzip on;', $mainConf);
        $this->assertStringContainsString('model/gltf-binary', $mainConf);
    }

    public function test_nginx_default_vhost_configured_for_large_uploads_and_fastcgi(): void
    {
        $vhostPath = base_path('docker/nginx/default.conf');
        $this->assertFileExists($vhostPath);
        $vhost = file_get_contents($vhostPath);

        // 3D/GLB Upload size & timeouts
        $this->assertStringContainsString('client_max_body_size 64M;', $vhost);
        $this->assertStringContainsString('fastcgi_buffer_size', $vhost);
        $this->assertStringContainsString('fastcgi_pass', $vhost);
        $this->assertStringContainsString('app:9000', $vhost);

        // Security headers
        $this->assertStringContainsString('X-Frame-Options', $vhost);
        $this->assertStringContainsString('X-Content-Type-Options', $vhost);

        // Health endpoint proxying
        $this->assertStringContainsString('location /up', $vhost);
    }
}
