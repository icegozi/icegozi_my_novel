<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ThreeEngineSetupTest extends TestCase
{
    protected string $engineDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engineDir = dirname(__DIR__, 2) . '/resources/js/3d-engine';
    }

    public function test_3d_engine_source_files_exist(): void
    {
        $this->assertFileExists($this->engineDir . '/projection-stage.js');
        $this->assertFileExists($this->engineDir . '/particle-system.js');
        $this->assertFileExists($this->engineDir . '/model-cache.js');
    }

    public function test_projection_stage_contains_required_api_contract(): void
    {
        $content = file_get_contents($this->engineDir . '/projection-stage.js');

        $this->assertStringContainsString('class ProjectionStage', $content);
        $this->assertStringContainsString('loadModel(', $content);
        $this->assertStringContainsString('setLighting(', $content);
        $this->assertStringContainsString('triggerEffect(', $content);
        $this->assertStringContainsString('setInteractive(', $content);
        $this->assertStringContainsString('destroy(', $content);
    }

    public function test_particle_system_supports_all_specified_particle_effects(): void
    {
        $content = file_get_contents($this->engineDir . '/particle-system.js');

        $this->assertStringContainsString('fireflies', $content);
        $this->assertStringContainsString('snow', $content);
        $this->assertStringContainsString('rain', $content);
        $this->assertStringContainsString('divine_sparks', $content);
        $this->assertStringContainsString('sakura', $content);
    }

    public function test_app_js_registers_3d_engine(): void
    {
        $appJsPath = dirname(__DIR__, 2) . '/resources/js/app.js';
        $content = file_get_contents($appJsPath);

        $this->assertStringContainsString('ProjectionStage', $content);
    }
}
