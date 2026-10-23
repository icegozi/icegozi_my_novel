<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_reader_layout_renders_default_meta_description_when_not_passed(): void
    {
        $response = $this->get('/settings/security');

        $response->assertStatus(302); // Redirects to login
        $loginResponse = $this->get('/login');
        $loginResponse->assertSee('<meta name="description" content="Novel Engine -', false);
    }

    public function test_header_logo_has_visible_focus_ring_and_kbd_has_high_contrast(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('group focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-xl', false);
        $response->assertSee('text-slate-600 dark:text-slate-300 bg-slate-200 dark:bg-slate-700', false);
    }
}
