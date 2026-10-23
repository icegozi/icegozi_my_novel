<?php

namespace Tests\Feature;

use Tests\TestCase;

class NotFoundAndAccessibilityTest extends TestCase
{
    public function test_not_found_page_renders_branded_view_with_recovery_links(): void
    {
        $response = $this->get('/non-existent-page-xyz');
        $response->assertStatus(404);
        $response->assertSee('Trang Không Tồn Tại');
        $response->assertSee('Trở Về Trang Chủ');
    }
}
