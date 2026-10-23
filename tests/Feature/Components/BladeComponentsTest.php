<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class BladeComponentsTest extends TestCase
{
    public function test_button_renders_button_and_link_with_minimal_props(): void
    {
        $btn = $this->blade('<x-button variant="primary">Khám Phá</x-button>');
        $btn->assertSee('Khám Phá');
        $btn->assertSee('bg-indigo-600');
        $btn->assertSee('focus-visible:ring-2');

        $link = $this->blade('<x-button href="/doc-truyen" variant="ghost">Đọc Ngay</x-button>');
        $link->assertSee('<a', false);
        $link->assertSee('href="/doc-truyen"', false);
    }

    public function test_badge_renders_with_age_13_and_status_variants(): void
    {
        $ageBadge = $this->blade('<x-badge variant="age13">13+</x-badge>');
        $ageBadge->assertSee('13+');
        $ageBadge->assertSee('bg-cyan-500/15');

        $dotBadge = $this->blade('<x-badge variant="emerald" dot>Hoàn thành</x-badge>');
        $dotBadge->assertSee('Hoàn thành');
        $dotBadge->assertSee('rounded-full');
    }

    public function test_alert_renders_with_accessibility_role_and_dismiss_button(): void
    {
        $alert = $this->blade('<x-alert type="error">Có lỗi xảy ra!</x-alert>');
        $alert->assertSee('role="alert"', false);
        $alert->assertSee('Có lỗi xảy ra!');
        $alert->assertSee('aria-label="Đóng thông báo"', false);
    }

    public function test_card_renders_with_glassmorphism_and_optional_hover(): void
    {
        $card = $this->blade('<x-card hover glow><p>Nội dung thẻ</p></x-card>');
        $card->assertSee('Nội dung thẻ');
        $card->assertSee('hover:-translate-y-1');
        $card->assertSee('shadow-glow');
    }
}
