<?php

namespace Tests\Feature;

use Tests\TestCase;

class ZenToolbarInteractivityTest extends TestCase
{
    public function test_zen_toolbar_contains_accessible_controls(): void
    {
        $view = $this->blade("@include('reader.chapters.partials.zen_toolbar')");

        $view->assertSee('id="zen-toolbar"', false);
        $view->assertSee('id="btn-toggle-zen-mode"', false);
        $view->assertSee('id="btn-font-decrease"', false);
        $view->assertSee('id="btn-font-increase"', false);
        $view->assertDontSee('data-theme="dark"', false);
        $view->assertSee('data-theme="sepia"', false);
        $view->assertSee('data-theme="light"', false);
    }

    public function test_reader_controls_script_handles_zen_mode_and_font_resize(): void
    {
        $js = file_get_contents(resource_path('js/reader-controls.js'));
        $this->assertStringContainsString('btn-toggle-zen-mode', $js);
        $this->assertStringContainsString('zen-mode', $js);
        $this->assertStringContainsString('btn-font-decrease', $js);
        $this->assertStringContainsString('btn-font-increase', $js);
    }
}
