<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderNavigationPolishTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_view_contains_search_shortcut_kbd(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->actingAs($user);
        $view = $this->blade("@include('layouts.partials.header')");

        $view->assertSee('id="header-search-input"', false);
        $view->assertSee('⌘K');
        $view->assertSee('id="user-menu-container"', false);
    }

    public function test_app_js_handles_ctrl_k_and_click_outside(): void
    {
        $js = file_get_contents(resource_path('js/app.js'));
        $this->assertStringContainsString('header-search-input', $js);
        $this->assertStringContainsString('user-dropdown', $js);
    }
}
