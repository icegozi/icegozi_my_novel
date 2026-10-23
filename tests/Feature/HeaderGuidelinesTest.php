<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_menu_button_has_accessible_label_and_focus_ring(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('aria-label="Menu tài khoản cá nhân"', false);
        $response->assertSee('focus-visible:ring-2', false);
    }
}
