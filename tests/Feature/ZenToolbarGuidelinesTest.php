<?php

namespace Tests\Feature;

use Tests\TestCase;

class ZenToolbarGuidelinesTest extends TestCase
{
    public function test_zen_toolbar_has_accessible_labels_and_numeric_classes(): void
    {
        $view = $this->blade("@include('reader.chapters.partials.zen_toolbar')");

        $view->assertSee('aria-label="Chuyển sang giao diện tối"', false);
        $view->assertSee('aria-label="Chuyển sang giao diện giấy cổ"', false);
        $view->assertSee('aria-label="Chuyển sang giao diện sáng"', false);
        $view->assertSee('aria-label="Giảm kích thước phông chữ"', false);
        $view->assertSee('aria-label="Tăng kích thước phông chữ"', false);
        $view->assertSee('tabular-nums', false);
        $view->assertSee('aria-hidden="true"', false);
    }
}
