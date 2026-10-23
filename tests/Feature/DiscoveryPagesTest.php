<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_discovery_pages_render_successfully(): void
    {
        $this->seed();

        $this->get('/')->assertStatus(200)->assertSee('Truyện Đề Cử');
        $this->get('/latest')->assertStatus(200)->assertSee('Mới Cập Nhật');
        $this->get('/popular')->assertStatus(200)->assertSee('Bảng Xếp Hạng');
        $this->get('/genres')->assertStatus(200)->assertSee('Thể Loại');
        $this->get('/genres/tien-hiep')->assertStatus(200)->assertSee('Tiên Hiệp');
    }
}
