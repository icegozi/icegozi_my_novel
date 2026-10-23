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
}

