<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayoutConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_layout_file_exists_and_moderation_queue_renders_components(): void
    {
        $this->assertFileExists(resource_path('views/layouts/admin.blade.php'));

        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('admin.moderation.queue'));
        $response->assertStatus(200);
        $response->assertSee('NOVEL ADMIN', false);
        // Verify badge component styling rendered in output
        $response->assertSee('rounded-full', false);
    }
}
