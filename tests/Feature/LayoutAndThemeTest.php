<?php

namespace Tests\Feature;

use Tests\TestCase;

class LayoutAndThemeTest extends TestCase
{
    public function test_reader_layout_renders_with_meta_and_fonts(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Plus+Jakarta+Sans', false);
        $response->assertSee('theme-manager', false);
    }
}
