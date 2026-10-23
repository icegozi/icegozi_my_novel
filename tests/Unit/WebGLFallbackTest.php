<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class WebGLFallbackTest extends TestCase
{
    protected string $engineDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engineDir = dirname(__DIR__, 2) . '/resources/js/3d-engine';
    }

    public function test_webgl_fallback_file_exists(): void
    {
        $this->assertFileExists($this->engineDir . '/webgl-fallback.js');
    }

    public function test_webgl_capability_detector_contract(): void
    {
        $content = file_get_contents($this->engineDir . '/webgl-fallback.js');

        $this->assertStringContainsString('class WebGLCapabilityDetector', $content);
        $this->assertStringContainsString('isWebGLAvailable', $content);
        $this->assertStringContainsString('isLowEndDevice', $content);
        $this->assertStringContainsString('attachContextLostHandler', $content);
        $this->assertStringContainsString('render2dFallback', $content);
    }

    public function test_projection_stage_integrates_webglcontextlost_handling(): void
    {
        $stageContent = file_get_contents($this->engineDir . '/projection-stage.js');

        $this->assertStringContainsString('webglcontextlost', $stageContent);
    }
}
