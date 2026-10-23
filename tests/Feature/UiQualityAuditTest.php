<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiQualityAuditTest extends TestCase
{
    use RefreshDatabase;
    public function test_all_common_components_have_proper_accessibility_attributes(): void
    {
        // 1. Buttons have focus-visible rings
        $btn = $this->blade('<x-button>Nút bấm</x-button>');
        $btn->assertSee('focus-visible:ring-2', false);

        // 2. Modals have dialog role and aria-modal
        $modal = $this->blade('<x-modal name="demo"><p>A</p></x-modal>');
        $modal->assertSee('role="dialog"', false);
        $modal->assertSee('aria-modal="true"', false);

        // 3. Alerts have role="alert"
        $alert = $this->blade('<x-alert type="warning">Cảnh báo</x-alert>');
        $alert->assertSee('role="alert"', false);

        // 4. Rating has descriptive aria-label for screen readers
        $rating = $this->blade('<x-rating :score="4.8" />');
        $rating->assertSee('aria-label="Đánh giá 4.8 trên 5 sao"', false);
    }

    public function test_reader_home_renders_with_responsive_book_covers_and_touch_targets(): void
    {
        $this->seed();

        $response = $this->get('/');
        $response->assertStatus(200);

        // Verify touch manipulation or minimum height classes
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('touch-action: manipulation', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);

        // Verify aspect ratio class exists for CLS prevention
        $response->assertSee('aspect-[3/4]', false);
    }

    public function test_theme_color_contrast_tokens_are_strictly_defined(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        // Sepia parchment mode tokens
        $this->assertStringContainsString('--color-bg: #f5efe6', $css);
        $this->assertStringContainsString('--color-text: #2d2216', $css);

        // Dark cyber-fantasy mode tokens
        $this->assertStringContainsString('--color-bg: #0b0f19', $css);
        $this->assertStringContainsString('--color-text: #f8fafc', $css);

        // Light studio mode tokens
        $this->assertStringContainsString('--color-bg: #f8fafc', $css);
        $this->assertStringContainsString('--color-text: #0f172a', $css);
    }
}
