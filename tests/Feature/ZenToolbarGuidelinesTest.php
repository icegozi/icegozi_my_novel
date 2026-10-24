<?php

namespace Tests\Feature;

use Tests\TestCase;

class ZenToolbarGuidelinesTest extends TestCase
{
    public function test_zen_toolbar_has_accessible_labels_and_numeric_classes(): void
    {
        $rendered = view('reader.chapters.partials.zen_toolbar')->render();

        $this->assertStringNotContainsString('aria-label="Chuyển sang giao diện tối"', $rendered);
        $this->assertStringContainsString('aria-label="Chuyển sang giao diện màu vàng giấy cổ"', $rendered);
        $this->assertStringContainsString('aria-label="Chuyển sang giao diện sáng"', $rendered);
        $this->assertStringContainsString('aria-label="A- Giảm kích thước phông chữ"', $rendered);
        $this->assertStringContainsString('aria-label="A+ Tăng kích thước phông chữ"', $rendered);
        $this->assertStringContainsString('tabular-nums', $rendered);
        $this->assertStringContainsString('aria-hidden="true"', $rendered);
    }
}
