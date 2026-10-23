<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReaderCssGuidelinesTest extends TestCase
{
    public function test_reader_stage_css_does_not_use_transition_all(): void
    {
        $css = file_get_contents(resource_path('css/reader-stage.css'));

        $this->assertStringNotContainsString('transition: all', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
