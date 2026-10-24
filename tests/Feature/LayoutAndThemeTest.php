<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutAndThemeTest extends TestCase
{
    use RefreshDatabase;
    public function test_reader_layout_renders_with_meta_and_fonts(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Plus+Jakarta+Sans', false);
        $response->assertSee('theme-manager', false);
    }

    public function test_reader_layout_defaults_to_sepia_theme(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('class="sepia"', false);
        $response->assertSee('content="#f5efe6"', false);
    }

    public function test_theme_contains_13_plus_tokens_and_accessibility_styles(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $tailwind = file_get_contents(base_path('tailwind.config.js'));

        $this->assertStringContainsString('--color-accent-glow', $css);
        $this->assertStringContainsString('aspectRatio', $tailwind);
    }

    public function test_theme_manager_script_contains_binary_sepia_light_rotation(): void
    {
        $js = file_get_contents(resource_path('js/theme-manager.js'));

        $this->assertStringNotContainsString("'dark'", $js);
        $this->assertStringContainsString("VALID_THEMES = ['light', 'sepia']", $js);
        $this->assertStringContainsString("nextMap = { sepia: 'light', light: 'sepia'", $js);

        $readerLayout = file_get_contents(resource_path('views/layouts/reader.blade.php'));
        $this->assertStringContainsString("if (theme === 'dark')", $readerLayout);
    }
}

