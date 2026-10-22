<?php

namespace Tests\Feature;

use Tests\TestCase;

class DockerDeveloperErgonomicsTest extends TestCase
{
    public function test_makefile_exists_and_contains_standard_developer_targets(): void
    {
        $makefilePath = base_path('Makefile');
        $this->assertFileExists($makefilePath);

        $content = file_get_contents($makefilePath);
        $this->assertStringContainsString('up:', $content);
        $this->assertStringContainsString('down:', $content);
        $this->assertStringContainsString('build:', $content);
        $this->assertStringContainsString('bash:', $content);
        $this->assertStringContainsString('test:', $content);
        $this->assertStringContainsString('migrate:', $content);
        $this->assertStringContainsString('prod-build:', $content);
        $this->assertStringContainsString('prod-up:', $content);
    }

    public function test_deployment_guide_contains_architecture_and_operational_runbooks(): void
    {
        $guidePath = base_path('docs/deployment/docker-enterprise-guide.md');
        $this->assertFileExists($guidePath);

        $content = file_get_contents($guidePath);
        $this->assertStringContainsString('Architecture', $content);
        $this->assertStringContainsString('Production Deployment', $content);
        $this->assertStringContainsString('Backup and Disaster Recovery', $content);
        $this->assertStringContainsString('mysqldump', $content);
        $this->assertStringContainsString('GLB', $content);
    }
}
