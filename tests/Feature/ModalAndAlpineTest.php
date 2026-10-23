<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModalAndAlpineTest extends TestCase
{
    public function test_modal_component_renders_with_aria_and_alpine_bindings(): void
    {
        $view = $this->blade(
            '<x-modal name="testModal" title="Cửa Sổ Thử Nghiệm">Nội dung modal</x-modal>'
        );

        $view->assertSee('role="dialog"', false);
        $view->assertSee('aria-modal="true"', false);
        $view->assertSee('Cửa Sổ Thử Nghiệm');
        $view->assertSee('Nội dung modal');
    }

    public function test_app_js_imports_and_starts_alpine(): void
    {
        $appJs = file_get_contents(resource_path('js/app.js'));
        $this->assertStringContainsString('Alpine', $appJs);
        $this->assertStringContainsString('Alpine.start()', $appJs);
    }
}
